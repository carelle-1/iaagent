<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MetaAccountController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\AgentCategoryController;
use App\Http\Controllers\UserAgentController;
use App\Http\Controllers\UserAgentCalendarController;
use App\Http\Controllers\UserAgentIdentityController;
use App\Http\Controllers\UserAgentPublicationController;

Route::get('/', function () {
    return view('index');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/logout', [AuthController::class, 'logout']);

Route::middleware(['auth', 'prevent-back-history'])->group(function () {
    Route::get('/dashboard', function () {
        return auth()->user()->role === 'admin' 
            ? redirect()->route('admin.dashboard') 
            : redirect()->route('user.dashboard');
    })->name('dashboard');

    Route::get('/user/dashboard', function () {
        return view('user-dashboard');
    })->name('user.dashboard')->middleware('role:user');

    Route::get('/admin/dashboard', function () {
        $totalUsers = \App\Models\User::query()->where('role', 'user')->count();
        $newUsersMonth = \App\Models\User::query()->where('role', 'user')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $metaTotal = \App\Models\MetaAccount::query()->count();
        $metaConnected = \App\Models\MetaAccount::query()->where('status', 'connected')->count();
        $metaPending = \App\Models\MetaAccount::query()->where('status', 'pending')->count();
        $metaDisconnected = \App\Models\MetaAccount::query()->where('status', 'disconnected')->count();

        $attentionClients = \App\Models\MetaAccount::query()->with('user')
            ->whereHas('user', function ($q) { $q->where('role', 'user'); })
            ->whereIn('status', ['disconnected', 'pending'])
            ->latest()
            ->take(20)
            ->get();

        $frMonths = [1 => 'Janv.', 'Févr.', 'Mars', 'Avr.', 'Mai', 'Juin', 'Juil.', 'Août', 'Sept.', 'Oct.', 'Nov.', 'Déc.'];
        $growthLabels = [];
        $growthData = [];
        $maxGrowth = 0;
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $growthLabels[] = $frMonths[(int) $month->month] ?? '';
            $count = \App\Models\User::query()->where('role', 'user')
                ->whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year)
                ->count();
            $growthData[] = $count;
            if ($count > $maxGrowth) {
                $maxGrowth = $count;
            }
        }

        $clients = \App\Models\User::query()->where('role', 'user')
            ->with('metaAccounts')
            ->latest()
            ->get();

        $subscriptions = \App\Models\Subscription::query()->orderBy('sort_order')->latest()->get();

        $agents = \App\Models\Agent::with('subscriptions')->orderBy('name')->get();

        return view('admin-dashboard', compact(
            'totalUsers', 'newUsersMonth', 'metaTotal', 'metaConnected', 'metaPending', 'metaDisconnected',
            'attentionClients', 'growthLabels', 'growthData', 'maxGrowth', 'clients', 'subscriptions', 'agents'
        ));
    })->name('admin.dashboard')->middleware('role:admin');

    Route::get('/admin/clients/{id}', function ($id) {
        $client = \App\Models\User::with('metaAccounts')->where('role', 'user')->findOrFail($id);
        $fullName = trim(($client->first_name ?? '') . ' ' . ($client->last_name ?? ''));
        $display = $fullName !== '' ? $fullName : ($client->email ?? 'Client');
        $initials = strtoupper(mb_substr($client->first_name ?? ($client->email ?? 'C'), 0, 1) . mb_substr($client->last_name ?? '', 0, 1));
        $connectedCount = $client->metaAccounts->where('status', 'connected')->count();
        $totalMeta = $client->metaAccounts->count();
        return view('admin-client-detail', compact('client', 'display', 'initials', 'connectedCount', 'totalMeta'));
    })->name('admin.clients.show')->middleware('role:admin');

    Route::middleware(['role:admin'])->prefix('admin/subscriptions')->name('admin.subscriptions.')->group(function () {
        Route::get('/', [SubscriptionController::class, 'index'])->name('index');
        Route::get('/create', [SubscriptionController::class, 'create'])->name('create');
        Route::post('/', [SubscriptionController::class, 'store'])->name('store');
        Route::get('/{subscription}', [SubscriptionController::class, 'show'])->name('show');
        Route::get('/{subscription}/edit', [SubscriptionController::class, 'edit'])->name('edit');
        Route::put('/{subscription}', [SubscriptionController::class, 'update'])->name('update');
        Route::delete('/{subscription}', [SubscriptionController::class, 'destroy'])->name('destroy');
    });

    Route::middleware(['role:admin'])->prefix('admin/agents')->name('admin.agents.')->group(function () {
        Route::get('/', [AgentController::class, 'index'])->name('index');
        Route::get('/create', [AgentController::class, 'create'])->name('create');
        Route::post('/', [AgentController::class, 'store'])->name('store');
        Route::get('/{agent}', [AgentController::class, 'show'])->name('show');
        Route::get('/{agent}/edit', [AgentController::class, 'edit'])->name('edit');
        Route::put('/{agent}', [AgentController::class, 'update'])->name('update');
        Route::delete('/{agent}', [AgentController::class, 'destroy'])->name('destroy');
    });

    Route::middleware(['role:admin'])->prefix('admin/agent-categories')->name('admin.agent-categories.')->group(function () {
        Route::get('/', [AgentCategoryController::class, 'index'])->name('index');
        Route::get('/create', [AgentCategoryController::class, 'create'])->name('create');
        Route::post('/', [AgentCategoryController::class, 'store'])->name('store');
        Route::get('/{category}', [AgentCategoryController::class, 'show'])->name('show');
        Route::get('/{category}/edit', [AgentCategoryController::class, 'edit'])->name('edit');
        Route::put('/{category}', [AgentCategoryController::class, 'update'])->name('update');
        Route::delete('/{category}', [AgentCategoryController::class, 'destroy'])->name('destroy');
        Route::post('/{category}/assign-agents', [AgentCategoryController::class, 'assignAgents'])->name('assign-agents');
    });

    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('clients', function () {
            $totalUsers = \App\Models\User::query()->where('role', 'user')->count();
            $newUsersMonth = \App\Models\User::query()->where('role', 'user')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
            $metaTotal = \App\Models\MetaAccount::query()->count();
            $metaConnected = \App\Models\MetaAccount::query()->where('status', 'connected')->count();
            $metaPending = \App\Models\MetaAccount::query()->where('status', 'pending')->count();
            $metaDisconnected = \App\Models\MetaAccount::query()->where('status', 'disconnected')->count();

            $attentionClients = \App\Models\MetaAccount::query()->with('user')
                ->whereHas('user', function ($q) { $q->where('role', 'user'); })
                ->whereIn('status', ['disconnected', 'pending'])
                ->latest()
                ->take(20)
                ->get();

            $frMonths = [1 => 'Janv.', 'Févr.', 'Mars', 'Avr.', 'Mai', 'Juin', 'Juil.', 'Août', 'Sept.', 'Oct.', 'Nov.', 'Déc.'];
            $growthLabels = [];
            $growthData = [];
            $maxGrowth = 0;
            for ($i = 5; $i >= 0; $i--) {
                $month = now()->subMonths($i);
                $growthLabels[] = $frMonths[(int) $month->month] ?? '';
                $count = \App\Models\User::query()->where('role', 'user')
                    ->whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->count();
                $growthData[] = $count;
                if ($count > $maxGrowth) {
                    $maxGrowth = $count;
                }
            }

            $clients = \App\Models\User::query()->where('role', 'user')
                ->with('metaAccounts')
                ->latest()
                ->get();

            $subscriptions = \App\Models\Subscription::query()->orderBy('sort_order')->latest()->get();

            $agents = \App\Models\Agent::with('subscriptions')->orderBy('name')->get();

            return view('admin-dashboard', compact(
                'totalUsers', 'newUsersMonth', 'metaTotal', 'metaConnected', 'metaPending', 'metaDisconnected',
                'attentionClients', 'growthLabels', 'growthData', 'maxGrowth', 'clients', 'subscriptions', 'agents'
            ))->with('activeSection', 'clients');
        })->name('clients.index');

        Route::get('support', function () {
            $totalUsers = \App\Models\User::query()->where('role', 'user')->count();
            $newUsersMonth = \App\Models\User::query()->where('role', 'user')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
            $metaTotal = \App\Models\MetaAccount::query()->count();
            $metaConnected = \App\Models\MetaAccount::query()->where('status', 'connected')->count();
            $metaPending = \App\Models\MetaAccount::query()->where('status', 'pending')->count();
            $metaDisconnected = \App\Models\MetaAccount::query()->where('status', 'disconnected')->count();

            $attentionClients = \App\Models\MetaAccount::query()->with('user')
                ->whereHas('user', function ($q) { $q->where('role', 'user'); })
                ->whereIn('status', ['disconnected', 'pending'])
                ->latest()
                ->take(20)
                ->get();

            $frMonths = [1 => 'Janv.', 'Févr.', 'Mars', 'Avr.', 'Mai', 'Juin', 'Juil.', 'Août', 'Sept.', 'Oct.', 'Nov.', 'Déc.'];
            $growthLabels = [];
            $growthData = [];
            $maxGrowth = 0;
            for ($i = 5; $i >= 0; $i--) {
                $month = now()->subMonths($i);
                $growthLabels[] = $frMonths[(int) $month->month] ?? '';
                $count = \App\Models\User::query()->where('role', 'user')
                    ->whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->count();
                $growthData[] = $count;
                if ($count > $maxGrowth) {
                    $maxGrowth = $count;
                }
            }

            $clients = \App\Models\User::query()->where('role', 'user')
                ->with('metaAccounts')
                ->latest()
                ->get();

            $subscriptions = \App\Models\Subscription::query()->orderBy('sort_order')->latest()->get();

            $agents = \App\Models\Agent::with('subscriptions')->orderBy('name')->get();

            return view('admin-dashboard', compact(
                'totalUsers', 'newUsersMonth', 'metaTotal', 'metaConnected', 'metaPending', 'metaDisconnected',
                'attentionClients', 'growthLabels', 'growthData', 'maxGrowth', 'clients', 'subscriptions', 'agents'
            ))->with('activeSection', 'support');
        })->name('support.index');

        Route::get('securite', function () {
            $totalUsers = \App\Models\User::query()->where('role', 'user')->count();
            $newUsersMonth = \App\Models\User::query()->where('role', 'user')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
            $metaTotal = \App\Models\MetaAccount::query()->count();
            $metaConnected = \App\Models\MetaAccount::query()->where('status', 'connected')->count();
            $metaPending = \App\Models\MetaAccount::query()->where('status', 'pending')->count();
            $metaDisconnected = \App\Models\MetaAccount::query()->where('status', 'disconnected')->count();

            $attentionClients = \App\Models\MetaAccount::query()->with('user')
                ->whereHas('user', function ($q) { $q->where('role', 'user'); })
                ->whereIn('status', ['disconnected', 'pending'])
                ->latest()
                ->take(20)
                ->get();

            $frMonths = [1 => 'Janv.', 'Févr.', 'Mars', 'Avr.', 'Mai', 'Juin', 'Juil.', 'Août', 'Sept.', 'Oct.', 'Nov.', 'Déc.'];
            $growthLabels = [];
            $growthData = [];
            $maxGrowth = 0;
            for ($i = 5; $i >= 0; $i--) {
                $month = now()->subMonths($i);
                $growthLabels[] = $frMonths[(int) $month->month] ?? '';
                $count = \App\Models\User::query()->where('role', 'user')
                    ->whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->count();
                $growthData[] = $count;
                if ($count > $maxGrowth) {
                    $maxGrowth = $count;
                }
            }

            $clients = \App\Models\User::query()->where('role', 'user')
                ->with('metaAccounts')
                ->latest()
                ->get();

            $subscriptions = \App\Models\Subscription::query()->orderBy('sort_order')->latest()->get();

            $agents = \App\Models\Agent::with('subscriptions')->orderBy('name')->get();

            return view('admin-dashboard', compact(
                'totalUsers', 'newUsersMonth', 'metaTotal', 'metaConnected', 'metaPending', 'metaDisconnected',
                'attentionClients', 'growthLabels', 'growthData', 'maxGrowth', 'clients', 'subscriptions', 'agents'
            ))->with('activeSection', 'securite');
        })->name('securite.index');

        Route::get('systeme', function () {
            $totalUsers = \App\Models\User::query()->where('role', 'user')->count();
            $newUsersMonth = \App\Models\User::query()->where('role', 'user')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
            $metaTotal = \App\Models\MetaAccount::query()->count();
            $metaConnected = \App\Models\MetaAccount::query()->where('status', 'connected')->count();
            $metaPending = \App\Models\MetaAccount::query()->where('status', 'pending')->count();
            $metaDisconnected = \App\Models\MetaAccount::query()->where('status', 'disconnected')->count();

            $attentionClients = \App\Models\MetaAccount::query()->with('user')
                ->whereHas('user', function ($q) { $q->where('role', 'user'); })
                ->whereIn('status', ['disconnected', 'pending'])
                ->latest()
                ->take(20)
                ->get();

            $frMonths = [1 => 'Janv.', 'Févr.', 'Mars', 'Avr.', 'Mai', 'Juin', 'Juil.', 'Août', 'Sept.', 'Oct.', 'Nov.', 'Déc.'];
            $growthLabels = [];
            $growthData = [];
            $maxGrowth = 0;
            for ($i = 5; $i >= 0; $i--) {
                $month = now()->subMonths($i);
                $growthLabels[] = $frMonths[(int) $month->month] ?? '';
                $count = \App\Models\User::query()->where('role', 'user')
                    ->whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->count();
                $growthData[] = $count;
                if ($count > $maxGrowth) {
                    $maxGrowth = $count;
                }
            }

            $clients = \App\Models\User::query()->where('role', 'user')
                ->with('metaAccounts')
                ->latest()
                ->get();

            $subscriptions = \App\Models\Subscription::query()->orderBy('sort_order')->latest()->get();

            $agents = \App\Models\Agent::with('subscriptions')->orderBy('name')->get();

            return view('admin-dashboard', compact(
                'totalUsers', 'newUsersMonth', 'metaTotal', 'metaConnected', 'metaPending', 'metaDisconnected',
                'attentionClients', 'growthLabels', 'growthData', 'maxGrowth', 'clients', 'subscriptions', 'agents'
            ))->with('activeSection', 'systeme');
        })->name('systeme.index');
    });

    Route::middleware(['role:user'])->prefix('user/meta-accounts')->name('user.meta-accounts.')->group(function () {
        Route::get('/', [MetaAccountController::class, 'index'])->name('index');
        Route::post('/', [MetaAccountController::class, 'store'])->name('store');
        Route::get('/{metaAccount}', [MetaAccountController::class, 'show'])->name('show');
        Route::put('/{metaAccount}', [MetaAccountController::class, 'update'])->name('update');
        Route::delete('/{metaAccount}', [MetaAccountController::class, 'destroy'])->name('destroy');
        Route::post('/{metaAccount}/disconnect', [MetaAccountController::class, 'disconnect'])->name('disconnect');
        Route::post('/{metaAccount}/reconnect', [MetaAccountController::class, 'reconnect'])->name('reconnect');
    });

    Route::middleware(['role:user'])->prefix('user/publications')->name('user.publications.')->group(function () {
        Route::get('/validated', [UserAgentPublicationController::class, 'index'])->name('validated');
        Route::post('/{entry}/generate', [UserAgentPublicationController::class, 'generate'])->name('generate');
        Route::post('/{entry}/save', [UserAgentPublicationController::class, 'save'])->name('save');
        Route::post('/{entry}/visual', [UserAgentPublicationController::class, 'generateVisual'])->name('visual');
        Route::post('/{entry}/publish', [UserAgentPublicationController::class, 'publish'])->name('publish');
        Route::get('/{entry}/insights', [UserAgentPublicationController::class, 'insights'])->name('insights');
        Route::get('/{entry}/comments', [UserAgentPublicationController::class, 'comments'])->name('comments');
        Route::post('/{entry}/comments/reply', [UserAgentPublicationController::class, 'replyComment'])->name('comments.reply');
        Route::get('/visuals/{visual}', [UserAgentPublicationController::class, 'visualStatus'])->name('visuals.status');
    });

    Route::middleware(['role:user'])->prefix('user/agents')->name('user.agents.')->group(function () {
        Route::get('/', [UserAgentController::class, 'index'])->name('index');
        Route::post('/', [UserAgentController::class, 'store'])->name('store');
        Route::put('/{userAgent}', [UserAgentController::class, 'update'])->name('update');
        Route::post('/{userAgent}', [UserAgentController::class, 'update'])->name('update.post');
        Route::delete('/{userAgent}', [UserAgentController::class, 'destroy'])->name('destroy');
        Route::get('/admin-agents', [UserAgentController::class, 'adminAgents'])->name('admin-agents');
        Route::get('/photo/{filename}', [UserAgentController::class, 'servePhoto'])->name('photo');
        Route::get('/logo/{filename}', [UserAgentIdentityController::class, 'serveLogo'])->name('logo');
        Route::get('/identity-image/{filename}', [UserAgentIdentityController::class, 'serveImage'])->name('identity.images.serve');
        Route::get('/calendar/models', [UserAgentCalendarController::class, 'models'])->name('calendar.models');
        Route::get('/{userAgent}/calendar', [UserAgentCalendarController::class, 'index'])->name('calendar.index');
        Route::post('/{userAgent}/calendar/generate', [UserAgentCalendarController::class, 'generate'])->name('calendar.generate');
        Route::delete('/{userAgent}/calendar', [UserAgentCalendarController::class, 'destroy'])->name('calendar.destroy');
        Route::patch('/{userAgent}/calendar/{entry}', [UserAgentCalendarController::class, 'update'])->name('calendar.entries.update');
        Route::patch('/{userAgent}/calendar/{entry}/status', [UserAgentCalendarController::class, 'updateStatus'])->name('calendar.entries.status');
        Route::delete('/{userAgent}/calendar/{entry}', [UserAgentCalendarController::class, 'destroyEntry'])->name('calendar.entries.destroy');
        Route::get('/{userAgent}/identity', [UserAgentIdentityController::class, 'show'])->name('identity.show');
        Route::post('/{userAgent}/identity', [UserAgentIdentityController::class, 'store'])->name('identity.store');
        Route::post('/{userAgent}/identity/accounts', [UserAgentIdentityController::class, 'storeAccount'])->name('identity.accounts.store');
        Route::post('/{userAgent}/identity/images', [UserAgentIdentityController::class, 'uploadImages'])->name('identity.images.store');
        Route::delete('/{userAgent}/identity/images/{image}', [UserAgentIdentityController::class, 'destroyImage'])->name('identity.images.destroy');
        Route::put('/{userAgent}/identity/accounts/{account}', [UserAgentIdentityController::class, 'updateAccount'])->name('identity.accounts.update');
        Route::delete('/{userAgent}/identity/accounts/{account}', [UserAgentIdentityController::class, 'destroyAccount'])->name('identity.accounts.destroy');
        Route::post('/{userAgent}/contacts', [UserAgentIdentityController::class, 'storeContact'])->name('identity.contacts.store');
        Route::put('/{userAgent}/contacts/{contact}', [UserAgentIdentityController::class, 'updateContact'])->name('identity.contacts.update');
        Route::delete('/{userAgent}/contacts/{contact}', [UserAgentIdentityController::class, 'destroyContact'])->name('identity.contacts.destroy');
        Route::post('/{userAgent}/websites', [UserAgentIdentityController::class, 'storeWebsite'])->name('identity.websites.store');
        Route::put('/{userAgent}/websites/{website}', [UserAgentIdentityController::class, 'updateWebsite'])->name('identity.websites.update');
        Route::delete('/{userAgent}/websites/{website}', [UserAgentIdentityController::class, 'destroyWebsite'])->name('identity.websites.destroy');
    });
});
