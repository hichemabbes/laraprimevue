<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Annees\AnneeAdministrativeController;
use App\Http\Controllers\Annees\AnneeCalculController;
use App\Http\Controllers\Annees\AnneeFormationController;

use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;

use App\Http\Controllers\CalendrierController;
use App\Http\Controllers\DeletePasswordController;
use App\Http\Controllers\ListesController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;





use App\Http\Controllers\Formations\SpecialiteController;
use App\Http\Controllers\Formations\ThemeController;
use App\Http\Controllers\Formations\SessionFormationController;
use App\Http\Controllers\Formations\FormationController;
use App\Http\Controllers\Formations\ProgrammeSpecialiteController;
use App\Http\Controllers\Formations\ModuleSpecialiteController;
use App\Http\Controllers\Formations\DocumentProgrammeSpecialiteController;
use App\Http\Controllers\Formations\DocumentModuleSpecialiteController;
use App\Http\Controllers\Formations\ProgrammeThemeController;
use App\Http\Controllers\Formations\ModuleThemeController;
use App\Http\Controllers\Formations\DocumentProgrammeThemeController;
use App\Http\Controllers\Formations\DocumentModuleThemeController;





Route::get('/listes', [ListesController::class, 'index']);

Route::post('/store-super-admin', [RegisteredUserController::class, 'storeSuperAdmin']);
Route::post('/login/user', [RegisteredUserController::class, 'login']);
Route::post('/forgot-password', [PasswordResetLinkController::class, 'store']);
Route::post('/reset-password', [NewPasswordController::class, 'store']);
Route::get('/check-initial-setup', function () {
    return response()->json(['is_empty' => \App\Models\User::count() === 0]);
});

Route::middleware('auth:sanctum')->group(function () {




    Route::post('/logout', [RegisteredUserController::class, 'logout']);

    Route::get('/profile/data', [ProfileController::class, 'getProfileData']);
    Route::put('/profile/update', [ProfileController::class, 'updateProfile']);
    Route::post('/profile/update-password', [ProfileController::class, 'updatePassword']);
    Route::post('/profile/upload-photo', [PersonnelDCController::class, 'uploadPhoto']);
    Route::get('/user/{id}/role', [PersonnelDCController::class, 'getUserRole']);
    Route::get('/user/{id}/centre', [PersonnelDCController::class, 'getUserCentre']);
    Route::get('/user/centre', [RegisteredUserController::class, 'getUserCentre']);

    // Routes Users
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
    Route::get('/roles', [UserController::class, 'getRoles']);

   // Route::get('/centres', [UserController::class, 'getCentres']);
    Route::get('/users/trashed', [UserController::class, 'trashed']);
    Route::post('/users/restore/{id}', [UserController::class, 'restore']);
    Route::post('/users/import', [UserController::class, 'import']);
    Route::get('/users/export', [UserController::class, 'export']);
    Route::post('/users/{id}/photo', [UserController::class, 'updatePhoto']);
    Route::post('/users/password/{id}', [UserController::class, 'updatePassword']);
    Route::post('/users/avatar', [UserController::class, 'updateAvatar']);
    Route::get('/check-email', [UserController::class, 'checkEmail']);
    Route::post('/users/{id}/associate-centre', [UserController::class, 'associateCentre']);

     // Routes Roles
    Route::get('/roles', [RoleController::class, 'index']);
    Route::post('/roles', [RoleController::class, 'store']);
    Route::put('/roles/{id}', [RoleController::class, 'update']);
    Route::delete('/roles/{id}', [RoleController::class, 'destroy']);
    Route::post('/roles/importxls', [RoleController::class, 'importXLS']);
    Route::get('/roles/trashed', [RoleController::class, 'trashed']);
    Route::post('/roles/restore/{id}', [RoleController::class, 'restore']);

    Route::get('/delete-password/status', [DeletePasswordController::class, 'getPasswordStatus']);
    Route::post('/delete-password/set', [DeletePasswordController::class, 'setOrUpdatePassword']);
    Route::delete('/delete-password', [DeletePasswordController::class, 'deletePassword']);

    Route::get('/listes', [ListesController::class, 'index']);
    Route::post('/listes/create', [ListesController::class, 'store']);
    Route::get('/listes/{id}', [ListesController::class, 'show']);
    Route::put('/listes/{id}', [ListesController::class, 'update']);
    Route::get('/listes/{id}/options', [ListesController::class, 'getListe']);
    Route::delete('/listes/{id}', [ListesController::class, 'destroy']);
    Route::get('/lists/options', [ListesController::class, 'options']);

    Route::get('/annees-formation', [AnneeFormationController::class, 'indexAnneeFormation']);
    Route::get('/annees-formation/with-options', [AnneeFormationController::class, 'indexWithOptions']);
    Route::get('/annees-formation/active', [AnneeFormationController::class, 'getActiveAnneeFormation']);
    Route::get('/annees-formation/{id}', [AnneeFormationController::class, 'show']);
    Route::post('/annees-formation', [AnneeFormationController::class, 'storeAnneeFormation']);
    Route::put('/annees-formation/{id}', [AnneeFormationController::class, 'updateAnneeFormation']);
    Route::delete('/annees-formation/{id}', [AnneeFormationController::class, 'destroyAnneeFormation']);
    Route::post('/periodes-repos', [AnneeFormationController::class, 'storeReposFormation']);
    Route::put('/periodes-repos/{id}', [AnneeFormationController::class, 'updateReposFormation']);
    Route::delete('/periodes-repos/{id}', [AnneeFormationController::class, 'destroyReposFormation']);

    Route::get('/annees-administratives', [AnneeAdministrativeController::class, 'indexAnneeAdministrative']);
    Route::get('/annees-administratives/{id}', [AnneeAdministrativeController::class, 'show']);
    Route::post('/annees-administratives', [AnneeAdministrativeController::class, 'storeAnneeAdministrative']);
    Route::put('/annees-administratives/{id}', [AnneeAdministrativeController::class, 'updateAnneeAdministrative']);
    Route::delete('/annees-administratives/{id}', [AnneeAdministrativeController::class, 'destroyAnneeAdministrative']);
    Route::get('/annees-administratives/active', [AnneeAdministrativeController::class, 'getActiveAnneeAdministrative']);
    Route::get('/annees-administratives/{id}/periodes-repos', [AnneeAdministrativeController::class, 'getPeriodesRepos']);
    Route::post('/periodes-repos-administratif', [AnneeAdministrativeController::class, 'storeReposAdministratif']);
    Route::put('/periodes-repos-administratif/{id}', [AnneeAdministrativeController::class, 'updateReposAdministratif']);
    Route::delete('/periodes-repos-administratif/{id}', [AnneeAdministrativeController::class, 'destroyReposAdministratif']);




    Route::get('/annees-calcul', [AnneeCalculController::class, 'index']);

    Route::get('/calendrier', [CalendrierController::class, 'indexCalendrier']);
    Route::get('/calendrier/{calendrier}', [CalendrierController::class, 'show']);
    Route::post('/calendrier', [CalendrierController::class, 'storeCalendrier']);
    Route::put('/calendrier/{calendrier}', [CalendrierController::class, 'updateCalendrier']);
    Route::delete('/calendrier/{calendrier}', [CalendrierController::class, 'destroyCalendrier']);
    Route::get('/calendrier/today', [CalendrierController::class, 'getTodayEvents']);
    Route::get('/repos-formations', [CalendrierController::class, 'indexReposFormations']);


// Spécialités
    Route::get('/specialites', [SpecialiteController::class, 'index']);
    Route::get('/specialites/{id}', [SpecialiteController::class, 'show']);
    Route::post('/specialites', [SpecialiteController::class, 'store']);
    Route::put('/specialites/{id}', [SpecialiteController::class, 'update']);
    Route::delete('/specialites/{id}', [SpecialiteController::class, 'destroy']);

    // Thèmes
    Route::get('/themes', [ThemeController::class, 'index']);
    Route::get('/themes/{id}', [ThemeController::class, 'show']);
    Route::post('/themes', [ThemeController::class, 'store']);
    Route::put('/themes/{id}', [ThemeController::class, 'update']);
    Route::delete('/themes/{id}', [ThemeController::class, 'destroy']);

    // Sessions de formation
    Route::get('/sessions', [SessionFormationController::class, 'index']);
    Route::get('/sessions/{id}', [SessionFormationController::class, 'show']);
    Route::post('/sessions', [SessionFormationController::class, 'store']);
    Route::put('/sessions/{id}', [SessionFormationController::class, 'update']);
    Route::delete('/sessions/{id}', [SessionFormationController::class, 'destroy']);

    // Formations
    Route::get('/formations', [FormationController::class, 'index']);
    Route::get('/formations/{id}', [FormationController::class, 'show']);
    Route::post('/formations', [FormationController::class, 'store']);
    Route::put('/formations/{id}', [FormationController::class, 'update']);
    Route::delete('/formations/{id}', [FormationController::class, 'destroy']);

    /* Spécialités */
    Route::get('/specialites', [SpecialiteController::class, 'index']);

    /* Programmes (spécialités) */
    Route::get('programmes-specialites', [ProgrammeSpecialiteController::class, 'index']);
    Route::post('programmes-specialites', [ProgrammeSpecialiteController::class, 'store']);
    Route::get('programmes-specialites/{id}', [ProgrammeSpecialiteController::class, 'show']);
    Route::put('programmes-specialites/{id}', [ProgrammeSpecialiteController::class, 'update']);
    Route::delete('programmes-specialites/{id}', [ProgrammeSpecialiteController::class, 'destroy']);

    Route::get('modules-specialites', [ModuleSpecialiteController::class, 'index']);
    Route::post('modules-specialites', [ModuleSpecialiteController::class, 'store']);
    Route::get('modules-specialites/{id}', [ModuleSpecialiteController::class, 'show']);
    Route::put('modules-specialites/{id}', [ModuleSpecialiteController::class, 'update']);
    Route::delete('modules-specialites/{id}', [ModuleSpecialiteController::class, 'destroy']);

    Route::get('documents-programmes-specialites', [DocumentProgrammeSpecialiteController::class, 'index']);
    Route::post('documents-programmes-specialites', [DocumentProgrammeSpecialiteController::class, 'store']);
    Route::get('documents-programmes-specialites/{id}', [DocumentProgrammeSpecialiteController::class, 'show']);
    Route::put('documents-programmes-specialites/{id}', [DocumentProgrammeSpecialiteController::class, 'update']);
    Route::delete('documents-programmes-specialites/{id}', [DocumentProgrammeSpecialiteController::class, 'destroy']);
    Route::get('documents-programmes-specialites/{id}/download', [DocumentProgrammeSpecialiteController::class, 'download']);

    Route::get('documents-modules-specialites', [DocumentModuleSpecialiteController::class, 'index']);
    Route::post('documents-modules-specialites', [DocumentModuleSpecialiteController::class, 'store']);
    Route::get('documents-modules-specialites/{id}', [DocumentModuleSpecialiteController::class, 'show']);
    Route::put('documents-modules-specialites/{id}', [DocumentModuleSpecialiteController::class, 'update']);
    Route::delete('documents-modules-specialites/{id}', [DocumentModuleSpecialiteController::class, 'destroy']);
    Route::get('documents-modules-specialites/{id}/download', [DocumentModuleSpecialiteController::class, 'download']);



    // Programmes Thèmes
    Route::get('/programmes-themes', [ProgrammeThemeController::class, 'index']);
    Route::get('/programmes-themes/{id}', [ProgrammeThemeController::class, 'show']);
    Route::post('/programmes-themes', [ProgrammeThemeController::class, 'store']);
    Route::put('/programmes-themes/{id}', [ProgrammeThemeController::class, 'update']);
    Route::delete('/programmes-themes/{id}', [ProgrammeThemeController::class, 'destroy']);

    // Modules Thèmes
    Route::get('/modules-themes', [ModuleThemeController::class, 'index']);
    Route::get('/modules-themes/{id}', [ModuleThemeController::class, 'show']);
    Route::post('/modules-themes', [ModuleThemeController::class, 'store']);
    Route::put('/modules-themes/{id}', [ModuleThemeController::class, 'update']);
    Route::delete('/modules-themes/{id}', [ModuleThemeController::class, 'destroy']);

    // Documents Programmes Thèmes
    Route::get('/documents-programmes-themes', [DocumentProgrammeThemeController::class, 'index']);
    Route::get('/documents-programmes-themes/{id}', [DocumentProgrammeThemeController::class, 'show']);
    Route::post('/documents-programmes-themes', [DocumentProgrammeThemeController::class, 'store']);
    Route::put('/documents-programmes-themes/{id}', [DocumentProgrammeThemeController::class, 'update']);
    Route::delete('/documents-programmes-themes/{id}', [DocumentProgrammeThemeController::class, 'destroy']);

    // Documents Modules Thèmes
    Route::get('/documents-modules-themes', [DocumentModuleThemeController::class, 'index']);
    Route::get('/documents-modules-themes/{id}', [DocumentModuleThemeController::class, 'show']);
    Route::post('/documents-modules-themes', [DocumentModuleThemeController::class, 'store']);
    Route::put('/documents-modules-themes/{id}', [DocumentModuleThemeController::class, 'update']);
    Route::delete('/documents-modules-themes/{id}', [DocumentModuleThemeController::class, 'destroy']);






});
