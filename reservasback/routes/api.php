<?php
use App\Http\Controllers\AboutController;
use App\Http\Controllers\Admin\DashboardStatsController;
use App\Http\Controllers\Api\ExperienceController;
use App\Http\Controllers\Api\PlaceController;
use App\Http\Controllers\Api\TestimonioController;
use App\Http\Controllers\AssociationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CustomPackageController;
use App\Http\Controllers\ElectronicReceiptController;
use App\Http\Controllers\EntrepreneurCategoryController;
use App\Http\Controllers\EntrepreneurController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\HomepageSettingController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\TourController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReservationTourExtraController;
use App\Http\Controllers\TourDateController;
use App\Http\Controllers\TourExtraController;
use Illuminate\Http\Request;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

use Illuminate\Support\Facades\Route;

// Autenticación
Route::middleware([EnsureFrontendRequestsAreStateful::class])->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/sanctum/csrf-cookie', function () {
        return response()->json(['message' => 'CSRF cookie']);
    });
});

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::middleware('auth:sanctum')->get('/me', [AuthController::class, 'me']);
Route::middleware('auth:sanctum')->get('/user/{id}/roles', [AuthController::class, 'getUserRole']);

// Tours
Route::apiResource('tours', TourController::class);

// Reservas
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/reservations', [ReservationController::class, 'userReservations']);
    Route::get('/entrepreneur/{entrepreneurId}/reservations', [ReservationController::class, 'entrepreneurReservations']);
    Route::post('/reservations', [ReservationController::class, 'store']);
    Route::delete('/reservations/{id}', [ReservationController::class, 'destroy']);
});
Route::get('/reservations/{id}', [ReservationController::class, 'show']);
Route::get('/reservations', [ReservationController::class, 'index']);
Route::get('reservations/count', [ReservationController::class, 'count']);
Route::delete('/reservations/{id}', [ReservationController::class, 'destroy']);

// Pagos
Route::middleware('auth:sanctum')->get('/payments/for-entrepreneur', [PaymentController::class, 'indexForEntrepreneur']);

Route::apiResource('payments', PaymentController::class)->only(['index', 'store', 'show']);

// Página principal
Route::get('/home', [HomepageSettingController::class, 'active']);
Route::get('/home/public', [HomepageSettingController::class, 'public']);
Route::get('/home/all', [HomepageSettingController::class, 'index']);
Route::get('/home/active', [HomepageSettingController::class, 'active']);
Route::put('/home/{id}', [HomepageSettingController::class, 'update']);
Route::delete('/home/{id}', [HomepageSettingController::class, 'destroy']);
Route::post('/home/{id}/activate', [HomepageSettingController::class, 'activate']);
Route::post('/home', [HomepageSettingController::class, 'store']);
Route::post('/home/remove-image', [HomepageSettingController::class, 'removeImage']);
Route::put('/home/update', [HomepageSettingController::class, 'update']);
Route::middleware(['auth:sanctum', 'role:super-admin'])->group(function () {
    Route::get('/homepage-settings', [HomepageSettingController::class, 'show']);
    Route::post('/homepage-settings', [HomepageSettingController::class, 'update']);
});

// About
Route::get('/abouts', [AboutController::class, 'index']);
Route::post('/abouts', [AboutController::class, 'store']);
Route::put('/abouts/{id}', [AboutController::class, 'update']);
Route::delete('/abouts/{id}', [AboutController::class, 'destroy']);
Route::post('/abouts/{about}/activate', [AboutController::class, 'activate']);
Route::get('/abouts/active', [AboutController::class, 'active']);

// Contacto
Route::get('/contact', [ContactController::class, 'index']);
Route::middleware(['auth:sanctum', 'role:super-admin'])->group(function () {
    Route::post('/contact', [ContactController::class, 'store']);
    Route::put('/contact/{id}', [ContactController::class, 'update']);
});

// Galería
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('gallery', GalleryController::class);
});
Route::middleware(['auth:sanctum', 'role:super-admin'])->group(function () {
    Route::delete('/gallery/{id}', [GalleryController::class, 'destroy']);
});

// Lugares
Route::apiResource('places', PlaceController::class);
Route::get('places/count', [PlaceController::class, 'count']);

// Testimonios
Route::get('testimonios', [TestimonioController::class, 'index']);
Route::post('testimonios', [TestimonioController::class, 'store']);

// Experiencias
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('experiences', ExperienceController::class);
});
Route::get('experiences', [ExperienceController::class, 'index']);
Route::post('experiences', [ExperienceController::class, 'store']);
Route::put('experiences/{id}', [ExperienceController::class, 'update']);
Route::delete('experiences/{id}', [ExperienceController::class, 'destroy']);

// Emprendedores
Route::apiResource('entrepreneurs', EntrepreneurController::class);
Route::get('entrepreneurs/category/{categoryId}', [EntrepreneurController::class, 'byCategory']);
Route::get('entrepreneurs/{entrepreneur}/history', [EntrepreneurController::class, 'history']);
Route::get('/entrepreneurs/count', [EntrepreneurController::class, 'count']);
Route::put('/entrepreneurs/{entrepreneur}/toggle-status', [EntrepreneurController::class, 'toggleStatus']);
Route::get('/entrepreneurs/{entrepreneur_id}/categories', [EntrepreneurController::class, 'getCategories']);
Route::middleware('auth:sanctum')->post('/entrepreneurs', [EntrepreneurController::class, 'store']);
Route::middleware('auth:sanctum')->get('/entrepreneur/authenticated', [EntrepreneurController::class, 'showAuthenticatedEntrepreneur']);

// Asociaciones y Categorías
Route::get('/associations/count', [AssociationController::class, 'count'])->middleware('auth:sanctum');
Route::apiResource('associations', AssociationController::class);

Route::apiResource('categories', CategoryController::class);
Route::get('categories/count', [CategoryController::class, 'count']);

// Rutas para EntrepreneurCategory con clave compuesta
Route::get('entrepreneur-categories', [EntrepreneurCategoryController::class, 'index']);
Route::post('entrepreneur-categories', [EntrepreneurCategoryController::class, 'store']);

Route::get('entrepreneur-categories/{entrepreneur_id}/{category_id}', [EntrepreneurCategoryController::class, 'show']);
Route::put('entrepreneur-categories/{entrepreneur_id}/{category_id}', [EntrepreneurCategoryController::class, 'update']);
Route::delete('entrepreneur-categories/{entrepreneur_id}/{category_id}', [EntrepreneurCategoryController::class, 'destroy']);

// === PRODUCTOS ===
// Rutas públicas para listar y ver productos
Route::get('products', [ProductController::class, 'index']);
Route::get('products/{id}', [ProductController::class, 'show']);

// Rutas protegidas para gestionar productos (crear, actualizar, borrar, imágenes)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('products/my', [ProductController::class, 'myProducts']);
    Route::post('products', [ProductController::class, 'store']);
    Route::put('products/{id}', [ProductController::class, 'update']);
    Route::delete('products/{id}', [ProductController::class, 'destroy']);
    Route::post('products/{product}/images', [ProductController::class, 'addImage']);
    Route::delete('products/{product}/images/{image}', [ProductController::class, 'deleteImage']);
});


// Dashboard Admin
Route::middleware(['auth:sanctum', 'can:access-admin'])->get('/admin/dashboard-counts', [EntrepreneurController::class, 'counts']);


Route::get('/payments', [PaymentController::class, 'index']);
Route::post('/payments', [PaymentController::class, 'store']);
Route::get('/payments/{id}', [PaymentController::class, 'show']);
Route::post('/payments/{id}/confirm', [PaymentController::class, 'confirm']);
Route::post('/payments/{id}/reject', [PaymentController::class, 'reject']);

Route::middleware('auth:sanctum')->get('/me', function (Request $request) {
    return response()->json($request->user()); // ← SIN relaciones
});
Route::middleware('auth:sanctum')->put('/me/update', [AuthController::class, 'update']);

Route::get('/mi-ruta', function (Request $request) {
    $user = $request->user();
});
Route::middleware('auth:sanctum')->group(function () {
    Route::delete('/reservations/{id}', [ReservationController::class, 'destroy']);
});
Route::middleware('auth:sanctum')->get('/my-reservations', [ReservationController::class, 'userReservations']);
Route::middleware('auth:sanctum')->get('/entrepreneur/authenticated', [EntrepreneurController::class, 'showAuthenticatedEntrepreneur']);

Route::get('/entrepreneurs/{entrepreneur}', [EntrepreneurController::class, 'show']);
Route::get('entrepreneurs/{entrepreneur}/history', [EntrepreneurController::class, 'history']);

Route::get('/homepage-setting/active', [HomepageSettingController::class, 'active']);
Route::get('/about', [HomepageSettingController::class, 'active']);
Route::middleware('auth:sanctum')->get('/reservations/by-client', function () {
    return auth()->user()->reservations()->with('product')->get();
});

Route::post('/reservations/direct-sale', [ReservationController::class, 'directSale']);
Route::middleware(['auth:sanctum', 'role:super-admin|emprendedor'])->get('/users/search', [AuthController::class, 'search']);
Route::middleware('auth:sanctum')->get('/packages/my', [PackageController::class, 'myPackages']);
// ✅ Primero la ruta específica
Route::get('/users/search', [AuthController::class, 'search']);

// Luego la genérica
Route::get('/users/{id}', [AuthController::class, 'show']);
Route::get('/users/search', [AuthController::class, 'search']); // sin middleware
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('packages', PackageController::class);
});
Route::middleware('auth:sanctum')->group(function () {
    Route::get('custom-packages', [CustomPackageController::class, 'index']);
    Route::get('custom-packages/{id}', [CustomPackageController::class, 'show']);
    Route::post('custom-packages', [CustomPackageController::class, 'store']);
    Route::put('custom-packages/{id}', [CustomPackageController::class, 'update']);
    Route::delete('custom-packages/{id}', [CustomPackageController::class, 'destroy']);
});
Route::post('/reservations/direct-sale', [ReservationController::class, 'directSale']);
Route::get('/reservations/emprendedor/{id}', [ReservationController::class, 'entrepreneurReservations']);

Route::put('/reservations/{id}', [ReservationController::class, 'update']);
Route::middleware('auth:sanctum')->get('/custom-packages', [CustomPackageController::class, 'index']);
Route::prefix('boletas')->group(function () {
    Route::get('/generar/{reservationId}', [ElectronicReceiptController::class, 'generar']);
    Route::get('/cliente/{userId}', [ElectronicReceiptController::class, 'indexCliente']);
    Route::get('/emprendedor/{emprendedorId}', [ElectronicReceiptController::class, 'indexEmprendedor']);
});
Route::get('/abouts/{id}', [AboutController::class, 'show']);

Route::post('/boletas/{id}/enviar-correo', [ElectronicReceiptController::class, 'enviarCorreo']);
Route::post('/reservations/direct-sale', [ReservationController::class, 'directSale']);
Route::middleware('auth:sanctum')
      ->get('/me/entrepreneur', [EntrepreneurController::class, 'showAuthenticatedEntrepreneur']);

Route::middleware('auth:sanctum')
      ->get('/reservations/by-client', [ReservationController::class, 'userReservations']);
Route::get('/reservations/user-reservations', [ReservationController::class, 'userReservations'])->middleware('auth:sanctum');
Route::prefix('boletas')->group(function () {
    Route::get('/descargar/{id}', [ElectronicReceiptController::class, 'descargarPDF'])
         ->name('boleta.descargar');   // pública
});
Route::middleware('auth:sanctum')->post('/me/update', [AuthController::class, 'update']);

Route::middleware('auth:sanctum')
      ->get('/reservations/mine', [ReservationController::class, 'myEntrepreneurReservations'])
      ->name('reservations.mine');


Route::get('/reservations/{id}', [ReservationController::class, 'show'])
      ->whereNumber('id');
Route::middleware('auth:sanctum')
    ->get('/reservations/count/my', [ReservationController::class, 'entrepreneurCount']);
Route::middleware('auth:sanctum')->get('/entrepreneur/payments', [PaymentController::class, 'indexForEntrepreneur']);
Route::prefix('admin')->middleware('auth:sanctum')->group(function () {
    Route::get('/associations/count',   [DashboardStatsController::class, 'countAssociations']);
    Route::get('/categories/count',     [DashboardStatsController::class, 'countCategories']);
    Route::get('/entrepreneurs/count',  [DashboardStatsController::class, 'countEntrepreneurs']);
    Route::get('/reservations/count',   [DashboardStatsController::class, 'countReservations']);
    Route::get('/places/count',         [DashboardStatsController::class, 'countPlaces']);
    Route::get('/users/count',          [DashboardStatsController::class, 'countUsers']);
});

Route::get('/places/{id}/entrepreneurs', [PlaceController::class, 'entrepreneurs']);

Route::get('/products/by-entrepreneur/{id}', [ProductController::class, 'byEntrepreneur']);
Route::get('/custom-packages/{id}', [CustomPackageController::class, 'show']);
Route::middleware('auth:sanctum')->get('/entrepreneur/paquetes', [PackageController::class, 'myPackages']);
Route::middleware('auth:sanctum')
      ->get('/entrepreneur/paquetes', [PackageController::class, 'myPackages']);
// === EXTRAS DE TOUR ===
Route::get('tours/{tour}/extras', [TourExtraController::class, 'index']);
Route::post('tours/{tour}/extras', [TourExtraController::class, 'store']);
Route::get('tour-extras/{id}', [TourExtraController::class, 'show']);
Route::put('tour-extras/{id}', [TourExtraController::class, 'update']);
Route::delete('tour-extras/{id}', [TourExtraController::class, 'destroy']);

// === FECHAS DE TOUR ===
Route::get('tours/{tour}/dates', [TourDateController::class, 'index']);
Route::post('tours/{tour}/dates', [TourDateController::class, 'store']);
Route::get('tour-dates/{id}', [TourDateController::class, 'show']);
Route::put('tour-dates/{id}', [TourDateController::class, 'update']);
Route::delete('tour-dates/{id}', [TourDateController::class, 'destroy']);

// === RELACIÓN RESERVA <-> EXTRA DE TOUR ===
Route::post('reservation-tour-extra', [ReservationTourExtraController::class, 'store']);
Route::delete('reservation-tour-extra/{id}', [ReservationTourExtraController::class, 'destroy']);
Route::apiResource('tours', TourController::class);
Route::middleware('auth:sanctum')->post('/cart/checkout', [ReservationController::class, 'checkoutCart']);
