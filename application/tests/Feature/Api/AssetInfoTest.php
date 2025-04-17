<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use App\ThirdParty\CardanoClients\ICardanoClient;

class AssetInfoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Use array cache driver to isolate tests
        Cache::flush();
    }

    /** @test */
    public function asset_info_returns_metadata_when_found()
    {
        // Bind a fake Cardano client into the container
        $this->instance(ICardanoClient::class, new class implements ICardanoClient {
            public function getAssetMetadata(string $assetId): ?array
            {
                if ($assetId === 'valid-asset-id') {
                    return [
                        'name'     => 'Test Token',
                        'policyId' => 'policy123',
                        'image'    => 'ipfs://Qm12345',
                    ];
                }
                return null;
            }

            public function assetHodled(string $policyId, string $assetId, string $stakeKey): bool
            {
                return true;
            }
        });

        $payload = ['asset_id' => 'valid-asset-id'];

        $response = $this->postJson('/api/v1/asset-info', $payload);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['name', 'policyId', 'image'],
            ])
            ->assertJson([
                'data' => [
                    'name'     => 'Test Token',
                    'policyId' => 'policy123',
                    'image'    => 'ipfs://Qm12345',
                ],
            ]);
    }

    /** @test */
    public function asset_info_returns_validation_error_when_asset_id_missing()
    {
        $response = $this->postJson('/api/v1/asset-info', []);

        $response->assertStatus(400)
            ->assertJsonStructure(['error'])
            ->assertJsonFragment([
                'error' => ['The asset id field is required.'],
            ]);
    }

    /** @test */
    public function asset_info_returns_404_when_asset_not_found()
    {
        // Bind a fake Cardano client that returns null
        $this->instance(ICardanoClient::class, new class implements ICardanoClient {
            public function getAssetMetadata(string $assetId): ?array
            {
                return null;
            }
            public function assetHodled(string $policyId, string $assetId, string $stakeKey): bool
            {
                return true;
            }
        });

        $response = $this->postJson('/api/v1/asset-info', ['asset_id' => 'missing-id']);

        $response->assertStatus(404)
            ->assertJson([
                'error' => trans('asset not found'),
            ]);
    }
}
