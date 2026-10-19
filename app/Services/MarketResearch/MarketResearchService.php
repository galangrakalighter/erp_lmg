<?php

namespace App\Services\MarketResearch;

use LaravelEasyRepository\BaseService;

interface MarketResearchService extends BaseService{

    // Write something awesome :)
    public function getMarket(int $id, string|array $platform);
}
