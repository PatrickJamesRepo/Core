<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\ThirdParty\CardanoClients\BlockFrostClient;
use App\Models\User;

class AssetValidationTest extends TestCase
{
    /** @test */
    public function test_validating_asset_in_wallet()
    {
        $user = User::factory()->create();
        $assetId = 'example-asset-id';  // Use a valid Cardano asset ID for testing
        $policyId = 'example-policy-id';  // Use a valid Cardano policy ID for testing
        $stakeKey = 'user-stake-key';  // The stake key of the user

        $blockFrostClient = new BlockFrostClient();

        $result = $blockFrostClient->assetHodled($policyId, $assetId, $stakeKey);

        // Assert that the asset is valid and tied to the user's wallet
        $this->assertTrue($result);
    }
}
