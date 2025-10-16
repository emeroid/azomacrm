<?php

use App\Http\Controllers\AutoResponderController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\OrderCommunicationController;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\FormTemplateController;
use App\Http\Controllers\FormBuilderController;
use App\Http\Controllers\EmbedController;
use App\Http\Controllers\FormSubmitController;
use App\Http\Controllers\MessageSchedulerController;
use App\Http\Controllers\WaAnalyticsController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\WhatsAppDeviceController;
use App\Models\AutoResponder;

Route::get('/', function () {
    return redirect()->route('filament.admin.auth.login');
});

Route::get('/cache', function () {
    Artisan::call('optimize');
    Artisan::call('route:clear');
    Artisan::call('route:cache');
    Artisan::call('filament:optimize');
    
    return 'success';
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->group(function () {

    Route::get('/orders/follow-up', [OrderCommunicationController::class, 'index'])
        ->name('orders.chat');
    
    Route::post('/orders/{order}/communications', [OrderCommunicationController::class, 'store'])
        ->name('orders.communications.store');
        
    Route::put('/communications/{communication}/labels', [OrderCommunicationController::class, 'updateLabels'])
        ->name('communications.labels.update');
    
    Route::post('/order/status/{order}', [OrderCommunicationController::class, 'updateOrderStatus'])->name('status.update');
    Route::get('/delivery-agents', [OrderCommunicationController::class, 'getDeliveryAgents'])->name('delivery.agents');
    
    // Outcomes routes
    Route::post('/outcomes', [OrderCommunicationController::class, 'storeOutcome'])->name('outcomes.store');
    Route::delete('/outcomes/{outcome}', [OrderCommunicationController::class, 'deleteOutcome'])->name('outcomes.destroy');
    
    // Template routes
    Route::post('/templates/wa/store', [OrderCommunicationController::class, 'storeTemplate'])->name('templates.wa.store');
    Route::delete('/templates/{template}/wa', [OrderCommunicationController::class, 'deleteTemplate'])->name('templates.wa.destroy');

    Route::get('/orders/chat/load-more', [OrderCommunicationController::class, 'loadMore'])->name('orders.chat.load-more');

    Route::prefix('campaigns')->group(function () {
        Route::get('/create', [CampaignController::class, 'create'])->name('campaigns.create');
        Route::post('/store', [CampaignController::class, 'store'])->name('campaigns.store');
    });

    // This creates routes for index, create, and destroy
    Route::resource('devices', WhatsAppDeviceController::class)->only([
        'index', 'create', 'destroy'
    ]);

    Route::get('/devices/{sessionId}/status', [WhatsAppDeviceController::class, 'getDeviceStatus'])->name('devices.status.poll');
});

// Form Templates Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/template', [FormBuilderController::class, 'index'])->name('template.index');
    Route::prefix('builder')->group(function () {
        Route::post('/create/{template}', [FormBuilderController::class, 'createFromTemplate'])->name('template.create');
        Route::get('/{template}', [FormBuilderController::class, 'show'])->name('form.builder');
        Route::post('/fields/{template}', [FormBuilderController::class, 'storeField'])->name('form.builder.field.store');
        Route::put('/fields/{field}', [FormBuilderController::class, 'updateField'])->name('form.builder.field.update');
        Route::delete('/fields/{field}', [FormBuilderController::class, 'destroyField'])->name('form.builder.field.destroy');
        Route::post('/reorder', [FormBuilderController::class, 'reorder'])->name('form.builder.reorder');
        Route::post('/preview/{template}', [FormBuilderController::class, 'preview'])->name('form.builder.preview');
        Route::post('/embed/{template}', [FormBuilderController::class, 'generateEmbed'])->name('form.builder.embed');
        Route::post('/save', [FormBuilderController::class, 'saveForm'])->name('form.builder.save');
    });

    // New administrative endpoint to check all session statuses
    Route::get('/devices/gateway-status', [WebhookController::class, 'listAllSessionsFromGateway']);
    Route::get('/wa-analytics', [WaAnalyticsController::class, 'index'])->name('analytics.index');

});

// Embedded Forms Routes (no auth required)
Route::get('/embed/{slug}', [EmbedController::class, 'show'])->name('form.embed');
Route::post('/orders/submit/{slug}', [FormSubmitController::class, '__invoke'])->name('form.submit');

// Add these to your routes file
Route::post('/orders/submit/{slug}/save-draft', [FormSubmitController::class, 'saveDraft'])->name('form.save-draft');
Route::post('/orders/submit/{slug}/abandoned', [FormSubmitController::class, 'markAbandoned'])->name('form.mark-abandoned');

Route::prefix('webhook')->group(function () {
    Route::post('/qr-code-received', [WebhookController::class, 'handleQrCodeReceived']);
    Route::post('/connected', [WebhookController::class, 'handleDeviceConnected']);
    Route::post('/disconnected', [WebhookController::class, 'handleDeviceDisconnected']);
    Route::post('/message', [WebhookController::class, 'handleIncomingMessage']);
    Route::post('/message-status-update', [WebhookController::class, 'handleMessageStatusUpdate']);
});

Route::prefix('auto-responder')->group(function () {
    Route::get('/', [AutoResponderController::class, 'index'])->name('auto-responders.index');
    Route::post('/store', [AutoResponderController::class, 'store'])->name('auto-responders.store');
    Route::delete('/delete', [AutoResponderController::class, 'destroy'])->name('auto-responders.delete');
});

Route::prefix('scheduler')->group(function () {
    Route::get('/', [MessageSchedulerController::class, 'index'])->name('scheduler.index');
    Route::post('/store', [MessageSchedulerController::class, 'store'])->name('scheduler.store');
    Route::delete('/delete', [MessageSchedulerController::class, 'destroy'])->name('scheduler.delete');
    Route::get('/check-form-fields/{templateId}', [MessageSchedulerController::class, 'getPotentialWhatsappFields'])->name('scheduler.potential-fields');
});


require __DIR__.'/auth.php';
