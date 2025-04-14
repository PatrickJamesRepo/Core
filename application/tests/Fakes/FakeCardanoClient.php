<?php

namespace Tests\Fakes;

use App\ThirdParty\CardanoClients\ICardanoClient;

class FakeCardanoClient implements ICardanoClient
{
    /**
     * Simulate retrieving asset metadata.
     */
    public function getAssetMetadata(string $assetId): ?array
    {
        // Return dummy metadata for testing.
        return [
            'assetId'     => $assetId,
            'name'        => 'Test Asset',
            'description' => 'This is test asset metadata.',
        ];
    }

    /**
     * Simulate checking whether an asset is held.
     */
    public function assetHodled(string $policyId, string $assetId, string $stakeKey): bool
    {
        // For testing purposes, we'll assume a specific combination returns true.
        return ($assetId === 'valid-asset' && $stakeKey === 'test-stake');
    }
}
