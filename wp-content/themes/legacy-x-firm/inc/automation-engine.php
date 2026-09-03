<?php
if(!defined('ABSPATH'))exit;

function legacyx_automation_menu(){
 add_submenu_page('legacyx-firm-os','Automation Engine','Automation','legacyx_view_reports','legacyx-automation','legacyx_render_automation_engine');
}
add_action('admin_menu','legacyx_automation_menu',24);

function legacyx_automation_rules(){
 return array(
  'overdue_work'=>array('label'=>'Overdue Workflow Follow-up','description'=>'Creates an internal management action when client workflow is overdue.','default'=>1),
  'stalled_application'=>array('label'=>'Stalled Application Review','description'=>'Creates an internal review action when an active application has not been updated for 14 days.','default'=>1),
  'past_due_billing'=>array('label'=>'Past-Due Billing Review','description'=>'Creates an internal management action for billing records marked Past Due.','default'=>1),
  'waiting_client'=>array('label'=>'Waiting-on-Client Follow-up','description'=>'Creates an internal follow-up action for workflow waiting on the client.','default'=>1),
 );
}
function legacyx_automation_settings(){
 $saved=get_option('legacyx_automation_settings_v1',array());$out=array();foreach(legacyx_automation_rules() as $k=>$r)$out[$k]=isset($saved[$k])?(int)!empty($saved[$k]):$r['default'];return $out;
}
function legacyx_automation_log($message,$type='info'){
 $log=get_option('legacyx_automation_log_v1',array());array_unshift($log,array('time'=>current_time('mysql'),'type'=>$type,'message'=>sanitize_text_field($message)));$log=array_slice($log,0,40);update_option('legacyx_automation_log_v1',$log,false);
}
function legacyx_automation_existing_action($source_key){
 $q=get_posts(array('post_type'=>'legacyx_intel_action','post_status'=>'any','numberposts'=>1,'meta_key'=>'_legacyx_automation_key','meta_value'=>$source_key,'fields'=>'ids'));return !empty($q);
}
function legacyx_automation_create_action($title,$client_id,$priority,$source,$note,$key,$due=''){
 if(!post_type_exists('legacyx_intel_action')||legacyx_automation_existing_action($key))return 0;
 $id=wp_insert_post(array('post_type'=>'legacyx_intel_action','post_status'=>'publish','post_title'=>sanitize_text_field($title)));
 if(!$id||is_wp_error($id))return 0;
 $meta=array('client_profile_id'=>absint($client_id),'action_status'=>'Open','action_priority'=>sanitize_text_field($priority),'action_due_date'=>sanitize_text_field($due),'action_owner'=>'','action_source'=>sanitize_text_field($source),'action_note'=>sanitize_textarea_field($note),'automation_key'=>sanitize_text_field($key));
 foreach($meta as $k=>$v)update_post_meta($id,'_legacyx_'.$k,$v);return $id;
}
function legacyx_automation_scan(){
 $settings=legacyx_automation_settings();$created=0;$today=current_time('Y-m-d');
 if(!empty($settings['overdue_work'])){
  $items=get_posts(array('post_type'=>'legacyx_work_item','post_status'=>'any','numberposts'=>100,'meta_query'=>array(array('key'=>'_legacyx_due_date','value'=>$today,'compare'=>'<','type'=>'DATE'))));
  foreach($items as $item){$status=get_post_meta($item->ID,'_legacyx_work_status',true);if(in_array($status,array('Completed','Archived'),true))continue;$client=(int)get_post_meta($item->ID,'_legacyx_client_profile_id',true);$due=get_post_meta($item->ID,'_legacyx_due_date',true);$created+=(int)(bool)legacyx_automation_create_action('Overdue workflow: '.get_the_title($item),$client,'High','Automation · Overdue Workflow','Review the overdue workflow item and assign the appropriate staff follow-up.','work_overdue_'.$item->ID,$due);
  }
 }
 if(!empty($settings['waiting_client'])){
  $items=get_posts(array('post_type'=>'legacyx_work_item','post_status'=>'any','numberposts'=>100,'meta_key'=>'_legacyx_work_status','meta_value'=>'Waiting on Client'));
  foreach($items as $item){$client=(int)get_post_meta($item->ID,'_legacyx_client_profile_id',true);$created+=(int)(bool)legacyx_automation_create_action('Client follow-up: '.get_the_title($item),$client,'Normal','Automation · Waiting on Client','Confirm whether the client has responded and determine the next appropriate workflow step.','work_waiting_'.$item->ID);}
 }
 if(!empty($settings['past_due_billing'])){
  $items=get_posts(array('post_type'=>'legacyx_invoice','post_status'=>'any','numberposts'=>100,'meta_key'=>'_legacyx_status','meta_value'=>'Past Due'));
  foreach($items as $item){$client=(int)get_post_meta($item->ID,'_legacyx_client_profile_id',true);$created+=(int)(bool)legacyx_automation_create_action('Past-due billing review: '.get_the_title($item),$client,'High','Automation · Billing Review','Review the recorded billing status and determine the appropriate internal follow-up.','billing_past_due_'.$item->ID);}
 }
 if(!empty($settings['stalled_application'])){
  $cutoff=current_time('timestamp')-(14*DAY_IN_SECONDS);$items=get_posts(array('post_type'=>'legacyx_application','post_status'=>'any','numberposts'=>100,'orderby'=>'modified','order'=>'ASC'));
  foreach($items as $item){$status=get_post_meta($item->ID,'_legacyx_status',true);if(!in_array($status,array('Draft','Preparing','Ready','Submitted','Under Review','Additional Information Requested'),true))continue;if(get_post_modified_time('U',true,$item)>=$cutoff)continue;$client=(int)(get_post_meta($item->ID,'_legacyx_client_profile_id',true)?:get_post_meta($item->ID,'_legacyx_client_id',true));$created+=(int)(bool)legacyx_automation_create_action('Application review: '.get_the_title($item),$client,'High','Automation · Application Aging','Review the application record for readiness, missing information, or an appropriate next internal step.','application_stalled_'.$item->ID);}
 }
 update_option('legacyx_automation_last_run_v1',current_time('mysql'),false);legacyx_automation_log('Automation scan completed. '.$created.' new management action'.($created===1?'':'s').' created.','success');return $created;
}

function legacyx_automation_schedule(){if(!wp_next_scheduled('legacyx_automation_hourly'))wp_schedule_event(time()+300,'hourly','legacyx_automation_hourly');}
add_action('init','legacyx_automation_schedule',40);add_action('legacyx_automation_hourly','legacyx_automation_scan');

function legacyx_automation_save_settings(){if(!current_user_can('legacyx_view_reports'))wp_die('Access denied.');check_admin_referer('legacyx_automation_settings');$rules=legacyx_automation_rules();$save=array();foreach($rules as $k=>$r)$save[$k]=isset($_POST[$k])?1:0;update_option('legacyx_automation_settings_v1',$save,false);legacyx_automation_log('Automation rule settings updated.');wp_safe_redirect(admin_url('admin.php?page=legacyx-automation&updated=1'));exit;}
add_action('admin_post_legacyx_automation_save','legacyx_automation_save_settings');
function legacyx_automation_run_now(){if(!current_user_can('legacyx_view_reports'))wp_die('Access denied.');check_admin_referer('legacyx_automation_run');$created=legacyx_automation_scan();wp_safe_redirect(add_query_arg(array('page'=>'legacyx-automation','ran'=>1,'created'=>$created),admin_url('admin.php')));exit;}
add_action('admin_post_legacyx_automation_run','legacyx_automation_run_now');

function legacyx_render_automation_engine(){if(!current_user_can('legacyx_view_reports'))wp_die('You do not have access to Automation Engine.');$rules=legacyx_automation_rules();$settings=legacyx_automation_settings();$last=get_option('legacyx_automation_last_run_v1','Never');$log=get_option('legacyx_automation_log_v1',array());?>
<div class="wrap lxa"><section class="lxa-hero"><div><span>PHASE 18 · AUTOMATION ENGINE</span><h1>Operational Automation</h1><p>Safe internal automation for reminders, management actions, and follow-up. Sensitive decisions remain human-controlled.</p></div><div><small>Last Scan</small><strong><?php echo esc_html($last);?></strong></div></section><?php if(isset($_GET['ran'])):?><div class="notice notice-success is-dismissible"><p>Automation scan completed. <?php echo esc_html(absint($_GET['created']??0));?> new management actions created.</p></div><?php endif;?><div class="lxa-grid"><section><div class="lxa-head"><span>AUTOMATION RULES</span><h2>Active Internal Triggers</h2></div><form method="post" action="<?php echo esc_url(admin_url('admin-post.php'));?>"><input type="hidden" name="action" value="legacyx_automation_save"><?php wp_nonce_field('legacyx_automation_settings');?><?php foreach($rules as $key=>$rule):?><label class="lxa-rule"><input type="checkbox" name="<?php echo esc_attr($key);?>" value="1" <?php checked(!empty($settings[$key]));?>><span><strong><?php echo esc_html($rule['label']);?></strong><small><?php echo esc_html($rule['description']);?></small></span></label><?php endforeach;?><button class="button button-primary" type="submit">Save Automation Rules</button></form></section><aside><div class="lxa-head"><span>ENGINE CONTROL</span><h2>Run & Review</h2></div><form method="post" action="<?php echo esc_url(admin_url('admin-post.php'));?>"><input type="hidden" name="action" value="legacyx_automation_run"><?php wp_nonce_field('legacyx_automation_run');?><button class="button button-primary button-hero" type="submit">Run Engine Now</button></form><a class="lxa-link" href="<?php echo esc_url(admin_url('edit.php?post_type=legacyx_intel_action'));?>">Open Action Center →</a><a class="lxa-link" href="<?php echo esc_url(admin_url('admin.php?page=legacyx-intelligence'));?>">Open Intelligence →</a></aside></div><section class="lxa-log"><div class="lxa-head"><span>AUTOMATION LOG</span><h2>Recent Engine Activity</h2></div><?php if(!$log):?><p>No automation activity recorded yet.</p><?php else:foreach(array_slice($log,0,12) as $entry):?><div><strong><?php echo esc_html($entry['message']);?></strong><small><?php echo esc_html($entry['time']);?></small></div><?php endforeach;endif;?></section><section class="lxa-boundary"><strong>Automation boundary</strong><p>The engine creates internal workflow and management follow-up only. It does not approve funding, make credit decisions, provide investment decisions, make legal determinations, send money, or guarantee external outcomes.</p></section></div><?php }
function legacyx_automation_styles($hook){if($hook!=='firm-os_page_legacyx-automation')return;wp_register_style('legacyx-automation',false);wp_enqueue_style('legacyx-automation');$css='.lxa{max-width:1400px;margin:24px 24px 60px 4px;color:#171512}.lxa-hero{background:#171512;color:#fff;padding:36px 40px;display:flex;justify-content:space-between;gap:28px;align-items:end}.lxa-hero span,.lxa-head span{font-size:11px;font-weight:800;letter-spacing:.16em;color:#f05a16}.lxa-hero h1{font:40px Georgia,serif;margin:7px 0}.lxa-hero p{color:#c9c3ba;max-width:760px}.lxa-hero>div:last-child{text-align:right}.lxa-hero small{display:block;color:#aaa197}.lxa-hero strong{font:20px Georgia,serif}.lxa-grid{display:grid;grid-template-columns:2fr 1fr;gap:18px;margin-top:18px}.lxa-grid>section,.lxa-grid>aside,.lxa-log,.lxa-boundary{background:#fff;border:1px solid #ded8ce;padding:28px}.lxa-head h2{font:28px Georgia,serif;margin:5px 0 20px}.lxa-rule{display:flex;gap:14px;padding:16px 0;border-top:1px solid #ece7df}.lxa-rule input{margin-top:4px}.lxa-rule small{display:block;color:#746e65;margin-top:4px}.lxa-link{display:block;padding:14px 0;border-bottom:1px solid #ece7df;color:#171512;text-decoration:none;font-weight:800}.lxa-log{margin-top:18px}.lxa-log>div:not(.lxa-head){display:flex;justify-content:space-between;gap:20px;padding:13px 0;border-top:1px solid #ece7df}.lxa-log small{color:#746e65}.lxa-boundary{margin-top:18px}.lxa-boundary strong{font:22px Georgia,serif}.lxa-boundary p{color:#746e65}@media(max-width:800px){.lxa-grid{grid-template-columns:1fr}.lxa-hero{display:block}.lxa-hero>div:last-child{text-align:left;margin-top:20px}.lxa-log>div:not(.lxa-head){display:block}}';wp_add_inline_style('legacyx-automation',$css);}add_action('admin_enqueue_scripts','legacyx_automation_styles');
