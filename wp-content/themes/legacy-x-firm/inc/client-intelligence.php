<?php
if(!defined('ABSPATH'))exit;
function legacyx_client_intelligence_clamp($v){return max(0,min(100,(int)round($v)));}
function legacyx_client_intelligence_snapshot($client_id){
 $client_id=absint($client_id);if(!$client_id)return array();
 $workflow=function_exists('legacyx_get_client_workflow')?legacyx_get_client_workflow($client_id):array();
 $apps=function_exists('legacyx_get_client_applications')?legacyx_get_client_applications($client_id):array();
 $billing=function_exists('legacyx_get_client_billing')?legacyx_get_client_billing($client_id):array();
 $engagements=function_exists('legacyx_get_client_engagements')?legacyx_get_client_engagements($client_id):array();
 $w=function_exists('legacyx_workflow_counts')?legacyx_workflow_counts($client_id):array('open'=>0,'total'=>0);
 $a=function_exists('legacyx_application_counts')?legacyx_application_counts($client_id):array('active'=>0,'total'=>0);
 $b=function_exists('legacyx_billing_counts')?legacyx_billing_counts($client_id):array('open'=>0,'balance'=>0,'total'=>0);
 $e=function_exists('legacyx_engagement_counts')?legacyx_engagement_counts($client_id):array('active'=>0,'total'=>0);
 $completed=0;$waiting=0;$overdue=0;$today=current_time('Y-m-d');
 foreach($workflow as $item){$status=get_post_meta($item->ID,'_legacyx_work_status',true);$due=get_post_meta($item->ID,'_legacyx_due_date',true);if($status==='Completed')$completed++;if($status==='Waiting on Client')$waiting++;if($due&&$due<$today&&!in_array($status,array('Completed','Closed'),true))$overdue++;}
 $app_stalled=0;$cutoff=current_time('timestamp')-(14*DAY_IN_SECONDS);foreach($apps as $item){$status=get_post_meta($item->ID,'_legacyx_status',true);if(in_array($status,array('Draft','Preparing','Ready','Submitted','Under Review','Additional Information Requested'),true)&&get_post_modified_time('U',true,$item)<$cutoff)$app_stalled++;}
 $past_due=0;foreach($billing as $item)if(get_post_meta($item->ID,'_legacyx_status',true)==='Past Due')$past_due++;
 $delivery_total=max(1,(int)($w['total']??count($workflow)));$delivery=legacyx_client_intelligence_clamp(100-(($overdue*22)+($waiting*8))+min(20,($completed/$delivery_total)*20));
 $billing_score=legacyx_client_intelligence_clamp(100-($past_due*30)-(((float)($b['balance']??0)>0)?15:0));
 $service_score=legacyx_client_intelligence_clamp(($e['total']??0)>0?(($e['active']??0)/max(1,$e['total']))*100:70);
 $application_score=legacyx_client_intelligence_clamp(($a['total']??0)>0?100-($app_stalled*25):80);
 $overall=legacyx_client_intelligence_clamp(($delivery+$billing_score+$service_score+$application_score)/4);
 $priority='Stable';if($overall<50||$overdue>1||$past_due>0)$priority='Priority';elseif($overall<70||$overdue||$app_stalled)$priority='Watch';elseif($overall>=85)$priority='Strong';
 $recs=array();if($overdue)$recs[]='Resolve '.$overdue.' overdue workflow item'.($overdue===1?'':'s').' and confirm ownership of the next action.';if($past_due)$recs[]='Review past-due billing records and confirm the appropriate collection or account follow-up.';if($app_stalled)$recs[]='Review '.$app_stalled.' application record'.($app_stalled===1?'':'s').' with no update in at least 14 days.';if($waiting)$recs[]='Follow up on '.$waiting.' workflow item'.($waiting===1?'':'s').' currently waiting on the client.';if(($e['active']??0)===0&&($e['total']??0)>0)$recs[]='Review the service relationship because no recorded engagement is currently active.';if(!$recs)$recs[]='Maintain the current relationship cadence and keep client records, next actions, and service milestones current.';
 return compact('overall','priority','delivery','billing_score','service_score','application_score','overdue','waiting','app_stalled','past_due','recs');
}
function legacyx_render_client_intelligence($client_id){if(!current_user_can('legacyx_manage_clients'))return;$s=legacyx_client_intelligence_snapshot($client_id);if(!$s)return;?>
<section class="l360-intelligence"><div class="l360-head"><span>LEGACY X INTELLIGENCE</span><h2>Client Relationship Intelligence</h2></div><div class="l360-intel-top"><div><small>Relationship Intelligence</small><strong><?php echo esc_html($s['overall']);?></strong><em><?php echo esc_html($s['priority']);?></em></div><p>Explainable operating interpretation of this client's recorded service, workflow, application and billing activity.</p></div><div class="l360-intel-pillars"><?php foreach(array('Delivery'=>$s['delivery'],'Billing Operations'=>$s['billing_score'],'Service Activity'=>$s['service_score'],'Application Activity'=>$s['application_score']) as $label=>$score):?><div><span><?php echo esc_html($label);?></span><strong><?php echo esc_html($score);?></strong><i><b style="width:<?php echo esc_attr($score);?>%"></b></i></div><?php endforeach;?></div><div class="l360-intel-recs"><h3>Recommended Staff Focus</h3><?php foreach($s['recs'] as $i=>$rec):?><p><b><?php echo esc_html(sprintf('%02d',$i+1));?></b><span><?php echo esc_html($rec);?></span></p><?php endforeach;?></div><small class="l360-intel-boundary">Internal management aid only. These heuristic signals are not credit decisions, lender underwriting, legal advice, investment recommendations, guarantees, or predictions of client outcomes.</small></section>
<?php }
