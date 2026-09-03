<?php
if(!defined('ABSPATH'))exit;

function legacyx_intelligence_menu(){
 add_submenu_page('legacyx-firm-os','Legacy X Intelligence','Intelligence','legacyx_view_reports','legacyx-intelligence','legacyx_render_intelligence_dashboard');
}
add_action('admin_menu','legacyx_intelligence_menu',21);

function legacyx_intelligence_clamp($value){return max(0,min(100,(int)round($value)));}

function legacyx_intelligence_snapshot(){
 $clients=function_exists('legacyx_reports_count')?legacyx_reports_count('legacyx_client'):0;
 $assessments=function_exists('legacyx_reports_count')?legacyx_reports_count('legacyx_assessment'):0;
 $work=function_exists('legacyx_reports_count')?legacyx_reports_count('legacyx_work_item'):0;
 $open_work=function_exists('legacyx_reports_meta_count')?legacyx_reports_meta_count('legacyx_work_item','_legacyx_work_status',array('Open','In Progress','Waiting on Client','Submitted','Under Review')):0;
 $completed_work=function_exists('legacyx_reports_meta_count')?legacyx_reports_meta_count('legacyx_work_item','_legacyx_work_status',array('Completed')):0;
 $apps=function_exists('legacyx_reports_count')?legacyx_reports_count('legacyx_application'):0;
 $approved=function_exists('legacyx_reports_meta_count')?legacyx_reports_meta_count('legacyx_application','_legacyx_status',array('Approved','Funded')):0;
 $engagements=function_exists('legacyx_reports_count')?legacyx_reports_count('legacyx_engagement'):0;
 $active_engagements=function_exists('legacyx_reports_meta_count')?legacyx_reports_meta_count('legacyx_engagement','_legacyx_engagement_status',array('Onboarding','Active','Waiting on Client')):0;
 $invoices=get_posts(array('post_type'=>'legacyx_invoice','post_status'=>'any','numberposts'=>-1,'fields'=>'ids'));
 $billed=0;$paid=0;$outstanding=0;
 foreach($invoices as $id){$a=(float)get_post_meta($id,'_legacyx_amount',true);$p=(float)get_post_meta($id,'_legacyx_paid_amount',true);$billed+=$a;$paid+=$p;$outstanding+=max(0,$a-$p);}
 $collection=$billed>0?($paid/$billed)*100:100;
 $delivery_total=max(1,$work);$delivery_completion=($completed_work/$delivery_total)*100;
 $delivery_score=legacyx_intelligence_clamp(($delivery_completion*.65)+(($open_work<=max(1,$completed_work))?35:20));
 $revenue_score=legacyx_intelligence_clamp($collection);
 $growth_score=legacyx_intelligence_clamp(($clients>0?min(70,($assessments/max(1,$clients))*35):0)+min(30,$clients*3));
 $service_score=legacyx_intelligence_clamp($engagements>0?($active_engagements/$engagements)*100:0);
 $overall=legacyx_intelligence_clamp(($delivery_score+$revenue_score+$growth_score+$service_score)/4);
 $signals=function_exists('legacyx_executive_signals')?legacyx_executive_signals():array();
 $high=count(array_filter($signals,function($s){return ($s['level']??'')==='high';}));
 $medium=count(array_filter($signals,function($s){return ($s['level']??'')==='medium';}));
 $overall=legacyx_intelligence_clamp($overall-($high*7)-($medium*3));
 return array(
  'overall'=>$overall,'delivery'=>$delivery_score,'revenue'=>$revenue_score,'growth'=>$growth_score,'service'=>$service_score,
  'clients'=>$clients,'assessments'=>$assessments,'work'=>$work,'open_work'=>$open_work,'completed_work'=>$completed_work,
  'apps'=>$apps,'approved'=>$approved,'engagements'=>$engagements,'active_engagements'=>$active_engagements,
  'billed'=>$billed,'paid'=>$paid,'outstanding'=>$outstanding,'collection'=>$collection,'signals'=>$signals
 );
}

function legacyx_intelligence_band($score){
 if($score>=80)return array('Strong','The operating picture is currently strong across the measured Legacy X Firm OS signals.');
 if($score>=65)return array('Stable','The operating picture is stable, with identifiable opportunities for improvement.');
 if($score>=45)return array('Watch','Several measured areas deserve management attention.');
 return array('Priority','The current recorded operating picture contains multiple areas requiring management review.');
}

function legacyx_intelligence_recommendations($s){
 $r=array();
 if($s['delivery']<70)$r[]=array('Delivery','Review open workflow and concentrate staff effort on aging or blocked client work.','legacyx_work_item');
 if($s['revenue']<85)$r[]=array('Billing Operations','Review outstanding recorded balances and billing statuses before expanding new delivery commitments.','legacyx_invoice');
 if($s['growth']<60)$r[]=array('Growth','Strengthen assessment-to-client conversion and relationship follow-up using the existing intake pipeline.','legacyx_client');
 if($s['service']<65&&$s['engagements']>0)$r[]=array('Service Portfolio','Review inactive or paused engagements and clarify the next action for each relationship.','legacyx_engagement');
 if($s['apps']>0&&$s['approved']===0)$r[]=array('Applications','Review application readiness, documentation status, and aging records without implying funding outcomes.','legacyx_application');
 foreach($s['signals'] as $signal){if(($signal['level']??'')==='high')$r[]=array('Executive Attention',$signal['detail']??$signal['title'],'');}
 if(!$r)$r[]=array('Maintain','Continue monitoring the current operating cadence and preserve data quality across client, workflow, service, application, and billing records.','');
 return array_slice($r,0,6);
}

function legacyx_intelligence_pillar($label,$score,$copy){
 echo '<article class="lxi-pillar"><div><span>'.esc_html($label).'</span><strong>'.esc_html($score).'</strong></div><div class="lxi-track"><i style="width:'.esc_attr($score).'%"></i></div><p>'.esc_html($copy).'</p></article>';
}

function legacyx_render_intelligence_dashboard(){
 if(!current_user_can('legacyx_view_reports'))wp_die('You do not have access to Legacy X Intelligence.','Legacy X Firm',array('response'=>403));
 $s=legacyx_intelligence_snapshot();list($band,$band_copy)=legacyx_intelligence_band($s['overall']);$recs=legacyx_intelligence_recommendations($s);
 ?>
 <div class="wrap legacyx-intelligence">
  <section class="lxi-hero"><div><span>PHASE 17 · LEGACY X INTELLIGENCE</span><h1>Executive Intelligence Engine</h1><p>Transforms recorded Legacy X Firm OS activity into explainable operating signals, management priorities and recommended areas of review.</p></div><div class="lxi-score"><small>Operating Intelligence</small><strong><?php echo esc_html($s['overall']);?></strong><em><?php echo esc_html($band);?></em></div></section>
  <section class="lxi-band"><strong><?php echo esc_html($band);?> operating posture</strong><p><?php echo esc_html($band_copy);?></p></section>
  <div class="lxi-pillars">
   <?php legacyx_intelligence_pillar('Client Delivery',$s['delivery'],'Measures workflow completion and current delivery pressure from recorded work items.');?>
   <?php legacyx_intelligence_pillar('Billing Operations',$s['revenue'],'Uses the recorded paid-to-billed ratio as an operational collection signal, not audited revenue.');?>
   <?php legacyx_intelligence_pillar('Relationship Growth',$s['growth'],'Uses client-profile and executive-assessment activity as a directional growth signal.');?>
   <?php legacyx_intelligence_pillar('Service Portfolio',$s['service'],'Measures the share of recorded engagements currently onboarding, active, or waiting on client.');?>
  </div>
  <div class="lxi-grid">
   <section><div class="lxi-head"><span>MANAGEMENT INTERPRETATION</span><h2>Recommended Focus</h2></div><div class="lxi-recs"><?php foreach($recs as $i=>$r):$url=$r[2]?admin_url('edit.php?post_type='.$r[2]):admin_url('admin.php?page=legacyx-reports');?><a href="<?php echo esc_url($url);?>"><b><?php echo esc_html(sprintf('%02d',$i+1));?></b><div><strong><?php echo esc_html($r[0]);?></strong><p><?php echo esc_html($r[1]);?></p></div><span>Review →</span></a><?php endforeach;?></div></section>
   <aside><div class="lxi-head"><span>OPERATING SNAPSHOT</span><h2>What the Engine Sees</h2></div><dl><div><dt>Client relationships</dt><dd><?php echo esc_html($s['clients']);?></dd></div><div><dt>Open workflow</dt><dd><?php echo esc_html($s['open_work']);?></dd></div><div><dt>Active engagements</dt><dd><?php echo esc_html($s['active_engagements']);?></dd></div><div><dt>Applications</dt><dd><?php echo esc_html($s['apps']);?></dd></div><div><dt>Recorded collection</dt><dd><?php echo esc_html(round($s['collection']));?>%</dd></div><div><dt>Attention signals</dt><dd><?php echo esc_html(count($s['signals']));?></dd></div></dl></aside>
  </div>
  <section class="lxi-boundary"><strong>Intelligence boundary</strong><p>This engine is an internal management aid built from records stored in Legacy X Firm OS. Scores are heuristic operating indicators—not audited financial analysis, lender underwriting, credit decisions, legal advice, investment recommendations, guarantees, or predictions of client outcomes. Management retains judgment and decision authority.</p></section>
 </div>
 <?php
}

function legacyx_intelligence_styles($hook){
 if($hook!=='firm-os_page_legacyx-intelligence')return;
 wp_register_style('legacyx-intelligence',false);wp_enqueue_style('legacyx-intelligence');
 $css='.legacyx-intelligence{max-width:1400px;margin:24px 24px 60px 4px;color:#171512}.lxi-hero{background:#11100f;color:#fff;padding:38px 42px;display:flex;justify-content:space-between;gap:30px;align-items:flex-end}.lxi-hero>div>span,.lxi-head>span{font-size:11px;letter-spacing:.16em;font-weight:800;color:#f05a16}.lxi-hero h1{font:42px Georgia,serif;margin:8px 0}.lxi-hero p{max-width:780px;color:#c9c3ba}.lxi-score{text-align:right;min-width:180px}.lxi-score small,.lxi-score em{display:block;color:#c9c3ba}.lxi-score strong{font:60px Georgia,serif;color:#f05a16;line-height:1}.lxi-score em{font-style:normal;margin-top:7px}.lxi-band,.lxi-boundary,.lxi-grid section,.lxi-grid aside{background:#fff;border:1px solid #ded8ce;padding:26px;margin-top:18px}.lxi-band strong{font:24px Georgia,serif}.lxi-band p,.lxi-boundary p{color:#69645d}.lxi-pillars{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-top:18px}.lxi-pillar{background:#fff;border:1px solid #ded8ce;padding:22px}.lxi-pillar>div:first-child{display:flex;justify-content:space-between;gap:10px}.lxi-pillar span{font-weight:800}.lxi-pillar strong{font:30px Georgia,serif}.lxi-track{height:7px;background:#eee9e1;margin:17px 0}.lxi-track i{display:block;height:100%;background:#f05a16}.lxi-pillar p{color:#746e65;margin-bottom:0}.lxi-grid{display:grid;grid-template-columns:2fr 1fr;gap:18px}.lxi-head h2{font:30px Georgia,serif;margin:6px 0 20px}.lxi-recs a{display:grid;grid-template-columns:42px 1fr auto;gap:14px;align-items:center;text-decoration:none;color:#171512;border-top:1px solid #e4dfd6;padding:18px 0}.lxi-recs a>b{font:22px Georgia,serif;color:#f05a16}.lxi-recs p{margin:5px 0 0;color:#69645d}.lxi-recs a>span{font-weight:800}.lxi-grid dl{margin:0}.lxi-grid dl div{display:flex;justify-content:space-between;border-bottom:1px solid #e4dfd6;padding:15px 0}.lxi-grid dt{color:#625d55}.lxi-grid dd{font-weight:800}.lxi-boundary strong{font:22px Georgia,serif}@media(max-width:1000px){.lxi-pillars{grid-template-columns:repeat(2,1fr)}.lxi-grid{grid-template-columns:1fr}}@media(max-width:600px){.lxi-hero{display:block;padding:28px 24px}.lxi-hero h1{font-size:34px}.lxi-score{text-align:left;margin-top:24px}.lxi-pillars{grid-template-columns:1fr}.lxi-recs a{grid-template-columns:34px 1fr}.lxi-recs a>span{grid-column:2}}';
 wp_add_inline_style('legacyx-intelligence',$css);
}
add_action('admin_enqueue_scripts','legacyx_intelligence_styles');
