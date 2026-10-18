<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Integration;
use App\Models\LoginEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use PragmaRX\Google2FA\Google2FA;

class IntegrationController extends Controller
{
    public function webIndex()
    {
        $integrations = Auth::user()
            ->integrations()
            ->withCount('loginEvents')
            ->latest()
            ->get();

        return view('integrations', compact('integrations'));
    }

    public function index(Request $request)
    {
        $integrations = $request->user()
            ->integrations()
            ->latest()
            ->get()
            ->map(fn ($i) => $this->formatIntegration($i));

        return response()->json($integrations);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'url' => 'required|url|max:255',
        ]);

        $g2fa = new Google2FA();
        $host = parse_url($data['url'], PHP_URL_HOST) ?? $data['url'];

        $integration = $request->user()->integrations()->create([
            'name' => $data['name'],
            'url' => $data['url'],
            'secret_key' => $g2fa->generateSecretKey(),
            'favicon_url' => "https://www.google.com/s2/favicons?domain={$host}&sz=64",
        ]);

        return response()->json($this->formatIntegration($integration), 201);
    }

    public function destroy(Request $request, Integration $integration)
    {
        $this->authorizeOwner($request->user(), $integration);
        $integration->delete();

        return response()->json(['message' => 'Deleted']);
    }

    public function toggle(Request $request, Integration $integration)
    {
        $this->authorizeOwner($request->user(), $integration);
        $integration->update(['is_active' => ! $integration->is_active]);

        return response()->json($this->formatIntegration($integration->fresh()));
    }

    /** Sanctum: owner-triggered login event (dev / older clients). */
    public function loginEvent(Request $request, Integration $integration)
    {
        $this->authorizeOwner($request->user(), $integration);

        return $this->startLoginEvent($request, $integration);
    }

    /** Target App: authenticate with the integration secret, not the user's phone token. */
    public function publicLoginEvent(Request $request)
    {
        $integration = $this->integrationFromSecret($request);

        return $this->startLoginEvent($request, $integration);
    }

    public function publicEventStatus(Request $request, LoginEvent $event)
    {
        $integration = $this->integrationFromSecret($request);
        abort_unless($event->integration_id === $integration->id, 403);

        return response()->json($this->eventPayload($event->load('integration')));
    }

    public function gateShow(Integration $integration)
    {
        abort_unless($integration->is_active, 403);

        return view('gate', [
            'integration' => $integration,
            'isFirst' => ! $integration->hasCompletedFirstLogin(),
        ]);
    }

    public function gateAttempt(Request $request, Integration $integration)
    {
        abort_unless($integration->is_active, 403);

        $isFirst = ! $integration->hasCompletedFirstLogin();
        $data = $request->validate([
            'credential_username' => $isFirst ? 'required|string|max:255' : 'nullable|string|max:255',
            // shortcut: password is required on first visit but never stored; persist it encrypted if a Target App needs replay
            'credential_password' => $isFirst ? 'required|string|max:255' : 'nullable|string',
        ]);

        $event = $this->recordLoginAttempt(
            $integration,
            $request,
            $data['credential_username'] ?? null,
        );

        return response()->json($this->eventPayload($event));
    }

    public function gateEventStatus(Integration $integration, LoginEvent $event)
    {
        abort_unless($event->integration_id === $integration->id, 404);

        return response()->json($this->eventPayload($event->load('integration')));
    }

    public function respondToEvent(Request $request, LoginEvent $event)
    {
        abort_unless($event->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'decision' => 'required|in:approved,declined',
        ]);

        abort_unless($event->status === 'pending', 409, 'This login is no longer pending.');

        $event->update(['status' => $data['decision']]);

        return response()->json([
            'event_id' => $event->id,
            'status' => $event->status,
        ]);
    }

    public function history(Request $request)
    {
        $events = LoginEvent::with('integration:id,name,favicon_url')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->take(50)
            ->get();

        return response()->json($events);
    }

    public function pendingApprovals(Request $request)
    {
        $events = LoginEvent::with('integration:id,name,favicon_url')
            ->where('user_id', $request->user()->id)
            ->where('status', 'pending')
            ->latest()
            ->get();

        return response()->json($events);
    }

    public function totpCode(Request $request, Integration $integration)
    {
        $this->authorizeOwner($request->user(), $integration);
        $g2fa = new Google2FA();
        $code = $g2fa->getCurrentOtp($integration->secret_key);
        $remaining = 30 - (time() % 30);

        return response()->json(['code' => $code, 'expires_in' => $remaining]);
    }

    private function startLoginEvent(Request $request, Integration $integration)
    {
        if (! $integration->is_active) {
            return response()->json(['message' => 'Integration is disabled'], 403);
        }

        $data = $request->validate([
            'ip_address' => 'nullable|string|max:45',
            'device' => 'nullable|string|max:255',
            'credential_username' => 'nullable|string|max:255',
        ]);

        $event = $this->recordLoginAttempt(
            $integration,
            $request,
            $data['credential_username'] ?? null,
            $data['ip_address'] ?? null,
            $data['device'] ?? null,
        );

        return response()->json($this->eventPayload($event), 201);
    }

    private function recordLoginAttempt(
        Integration $integration,
        Request $request,
        ?string $username = null,
        ?string $ip = null,
        ?string $device = null,
    ): LoginEvent {
        $isFirst = ! $integration->hasCompletedFirstLogin();

        $event = LoginEvent::create([
            'integration_id' => $integration->id,
            'user_id' => $integration->user_id,
            'ip_address' => $ip ?? $request->ip(),
            'device' => $device ?? $request->userAgent(),
            'status' => $isFirst ? 'auto' : 'pending',
            'is_first_login' => $isFirst,
            'credential_username' => $username,
        ]);

        if (! $isFirst) {
            $this->sendPushApproval($integration->user, $integration, $event);
        }

        $event->setRelation('integration', $integration);

        return $event;
    }

    private function eventPayload(LoginEvent $event): array
    {
        $allowed = in_array($event->status, ['approved', 'auto'], true);

        return [
            'event_id' => $event->id,
            'is_first' => (bool) $event->is_first_login,
            'status' => $event->status,
            'requires_approval' => $event->status === 'pending',
            'redirect_url' => $allowed ? $event->integration->url : null,
        ];
    }

    private function integrationFromSecret(Request $request): Integration
    {
        $secret = $request->input('secret_key') ?? $request->header('X-Integration-Secret');
        abort_unless(is_string($secret) && $secret !== '', 401, 'Invalid secret key');

        $integration = Integration::where('secret_key', $secret)->first();
        abort_unless($integration, 401, 'Invalid secret key');

        return $integration;
    }

    private function authorizeOwner($user, Integration $integration): void
    {
        abort_unless($integration->user_id === $user->id, 403);
    }

    private function formatIntegration(Integration $i): array
    {
        return [
            'id' => $i->id,
            'name' => $i->name,
            'url' => $i->url,
            'secret_key' => $i->secret_key,
            'gate_token' => $i->gate_token,
            'gate_url' => $i->gateUrl(),
            'is_active' => $i->is_active,
            'favicon_url' => $i->favicon_url ?? $i->favicon,
            'created_at' => $i->created_at,
        ];
    }

    private function sendPushApproval($user, Integration $integration, LoginEvent $event): void
    {
        $devices = Device::where('user_id', $user->id)
            ->whereNotNull('expo_push_token')
            ->pluck('expo_push_token');

        if ($devices->isEmpty()) {
            return;
        }

        $messages = $devices->map(fn ($token) => [
            'to' => $token,
            'title' => '🔐 Login Attempt Detected',
            'body' => "Someone is trying to log in to {$integration->name}. Approve?",
            'data' => [
                'event_id' => $event->id,
                'integration_name' => $integration->name,
                'type' => 'login_approval',
            ],
            'sound' => 'default',
            'priority' => 'high',
        ])->values()->toArray();

        try {
            Http::post('https://exp.host/--/api/v2/push/send', $messages);
        } catch (\Throwable $e) {
            Log::warning('Push notification failed: '.$e->getMessage());
        }
    }
}
