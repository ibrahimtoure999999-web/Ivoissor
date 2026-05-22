<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OcrTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * Un invité ne peut pas accéder à l'endpoint OCR (bloqué par CSRF ou Auth).
     */
    public function test_guest_cannot_access_ocr_analyze()
    {
        $response = $this->postJson(route('demandes.ocr-analyze'), [
            'document' => UploadedFile::fake()->image('cni.jpg')
        ]);

        // Sur une route 'web', le CSRF passe souvent avant l'Auth.
        // On accepte 401 ou 419 comme preuve que l'accès est restreint.
        $this->assertTrue(in_array($response->status(), [401, 419]));
    }

    /**
     * Un citoyen connecté peut téléverser un fichier et recevoir des données simulées.
     */
    public function test_authenticated_user_can_analyze_document_via_ocr()
    {
        // On bypass les middlewares pour se concentrer sur la logique du contrôleur/service
        $this->withoutMiddleware();
        $this->actingAs($this->user);

        $response = $this->postJson(route('demandes.ocr-analyze'), [
            'document' => UploadedFile::fake()->image('cni_kouadio.jpg')
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'data' => [
                         'found' => true,
                         'nom' => 'KOUADIO',
                         'prenoms' => 'Marc Koffi'
                     ]
                 ]);

        // Vérifier le log d'audit
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->user->id,
            'action' => 'OCR_SCAN'
        ]);
    }

    /**
     * La validation rejette les formats de fichiers non supportés.
     */
    public function test_ocr_rejects_invalid_file_formats()
    {
        $this->withoutMiddleware();
        $this->actingAs($this->user);

        $response = $this->postJson(route('demandes.ocr-analyze'), [
            'document' => UploadedFile::fake()->create('document.txt', 100)
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['document']);
    }

    /**
     * L'OCR gère les documents inconnus (pas de correspondance simulée).
     */
    public function test_ocr_handles_unknown_documents()
    {
        $this->withoutMiddleware();
        $this->actingAs($this->user);

        $response = $this->postJson(route('demandes.ocr-analyze'), [
            'document' => UploadedFile::fake()->image('random_pic.jpg')
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'data' => [
                         'found' => false,
                         'nom' => ''
                     ]
                 ]);
    }

    /**
     * L'OCR gère correctement les accents (traoré vs traore).
     */
    public function test_ocr_normalizes_accents_in_filename()
    {
        $this->withoutMiddleware();
        $this->actingAs($this->user);

        $response = $this->postJson(route('demandes.ocr-analyze'), [
            'document' => UploadedFile::fake()->image('passeport_traoré.jpg')
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'data' => [
                         'found' => true,
                         'nom' => 'TRAORÉ',
                         'prenoms' => 'Mariam'
                     ]
                 ]);
    }

    /**
     * L'OCR nettoie les tags XSS du nom de fichier dans l'audit log.
     */
    public function test_ocr_filters_xss_tags_from_filename_in_audit_log()
    {
        $this->withoutMiddleware();
        $this->actingAs($this->user);

        $filename = "<script>alert('XSS')</script>cni_kouadio.jpg";
        $response = $this->postJson(route('demandes.ocr-analyze'), [
            'document' => UploadedFile::fake()->image($filename)
        ]);

        $response->assertStatus(200);

        // Récupérer le dernier log d'audit
        $log = AuditLog::where('action', 'OCR_SCAN')->orderBy('id', 'desc')->first();
        expect($log)->not->toBeNull();
        expect($log->description)->not->toContain('<script>');
        expect($log->description)->not->toContain('</script>');
        expect($log->description)->toContain("cni_kouadio.jpg");
    }
}
