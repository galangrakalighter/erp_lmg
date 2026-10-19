<?php
namespace App\Services;
use Illuminate\Contracts\Auth\Authenticatable;
final class MarketResearchMutationAudit { public function __construct(private readonly ActivityLogService $log) {} public function record(Authenticatable $u,string $entity,string $action,int $id,int $client,array $data=[]): void { $this->log->record(['action'=>strtoupper($action),'module'=>'market_research_'.$entity,'target_id'=>$id,'target_type'=>$entity,'description'=>'Market research mutation '.$action,'context'=>['client_id'=>$client,'tool'=>'marketing_'.strtolower($action).'_'.$entity,'record_id'=>$id,'result'=>'success','status'=>'completed','occurred_at'=>now()->toIso8601String()],'new_data'=>in_array(strtoupper($action),['CREATE','ARCHIVE'],true)?[]:$data],$u); } }
