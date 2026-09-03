<?php
if(!defined('ABSPATH'))exit;
function legacyx_signal_count_query($args){$q=new WP_Query(array_merge(array('post_status'=>'any','posts_per_page'=>1,'fields'=>'ids'),$args));return (int)$q->found_posts;}
function legacyx_executive_signals(){
 $today=current_time('Y-m-d');$now=current_time('timestamp');$stalled_before=date('Y-m-d H:i:s',$now-(14*DAY_IN_SECONDS));$inactive_before=date('Y-m-d H:i:s',$now-(90*DAY_IN_SECONDS));
 $signals=array();
 $overdue=legacyx_signal_count_query(array('post_type'=>'legacyx_work_item','meta_query'=>array('relation'=>'AND',array('key'=>'_legacyx_work_status','value'=>array('Completed','Closed'),'compare'=>'NOT IN'),array('key'=>'_legacyx_due_date','value'=>$today,'compare'=>'<','type'=>'DATE'))));
 if($overdue)$signals[]=array('level'=>'high','title'=>'Overdue workflow','count'=>$overdue,'detail'=>'Open workflow items have passed their recorded due date.','url'=>admin_url('edit.php?post_type=legacyx_work_item'));
 $pastdue=legacyx_reports_meta_count('legacyx_invoice','_legacyx_status',array('Past Due'));
 if($pastdue)$signals[]=array('level'=>'high','title'=>'Past-due billing','count'=>$pastdue,'detail'=>'Recorded billing items require collection or status review.','url'=>admin_url('edit.php?post_type=legacyx_invoice'));
 $stalled=legacyx_signal_count_query(array('post_type'=>'legacyx_application','date_query'=>array(array('before'=>$stalled_before,'column'=>'post_modified','inclusive'=>true)),'meta_query'=>array(array('key'=>'_legacyx_status','value'=>array('Draft','Preparing','Ready','Submitted','Under Review','Additional Information Requested'),'compare'=>'IN'))));
 if($stalled)$signals[]=array('level'=>'medium','title'=>'Stalled applications','count'=>$stalled,'detail'=>'Active application records have not been modified in at least 14 days.','url'=>admin_url('edit.php?post_type=legacyx_application'));
 $waiting=legacyx_reports_meta_count('legacyx_work_item','_legacyx_work_status',array('Waiting on Client'));
 if($waiting)$signals[]=array('level'=>'medium','title'=>'Waiting on client','count'=>$waiting,'detail'=>'Workflow items are paused pending client action.','url'=>admin_url('edit.php?post_type=legacyx_work_item'));
 $inactive=legacyx_signal_count_query(array('post_type'=>'legacyx_client','date_query'=>array(array('before'=>$inactive_before,'column'=>'post_modified','inclusive'=>true))));
 if($inactive)$signals[]=array('level'=>'low','title'=>'Inactive relationships','count'=>$inactive,'detail'=>'Client profiles have not been updated in at least 90 days and may need relationship review.','url'=>admin_url('edit.php?post_type=legacyx_client'));
 return $signals;
}
function legacyx_render_executive_signals(){if(!current_user_can('legacyx_view_reports'))return;$signals=legacyx_executive_signals();echo '<section class="legacyx-signal-section"><div class="legacyx-report-head"><span>EXECUTIVE ATTENTION</span><h2>Action Signals</h2><p>Operational exceptions surfaced from Legacy X Firm OS records so management can prioritize follow-up.</p></div>';if(!$signals){echo '<div class="legacyx-signal-clear"><strong>No current exception signals</strong><span>The monitored operational thresholds do not currently show overdue, stalled, or inactive records.</span></div>';}else{echo '<div class="legacyx-signal-grid">';foreach($signals as $s){echo '<a class="legacyx-signal '.esc_attr($s['level']).'" href="'.esc_url($s['url']).'"><span>'.esc_html(strtoupper($s['level'])).' PRIORITY</span><strong>'.esc_html($s['count']).'</strong><h3>'.esc_html($s['title']).'</h3><p>'.esc_html($s['detail']).'</p></a>';}echo '</div>';}echo '<p class="legacyx-small-note">Signals are operational prompts based on recorded dates/statuses. They are not financial, legal, underwriting, investment, or credit decisions.</p></section>';}
