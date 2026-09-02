<?php
if (!defined('ABSPATH')) { exit; }

function legacyx_creditos_defaults() {
    return array(
        'personal_stage' => 'Not Started',
        'business_stage' => 'Not Started',
        'personal_score' => '',
        'business_status' => 'Not Established',
        'funding_goal' => '',
        'next_action' => 'Complete your Credit & Capital intake with your Legacy X Firm advisor.',
        'personal_steps' => array(
            'Profile & Goal Review','Reports & Data Review','Accuracy & Dispute Review','Utilization & Debt Strategy','Positive Credit Building','Funding Readiness','Monitoring & Maintenance'
        ),
        'business_steps' => array(
            'Entity & Compliance Review','Business Identity Foundation','Business Banking & Cash Flow','Vendor & Trade Credit','Business Bureau Profiles','Funding Readiness','Monitoring & Growth'
        )
    );
}

function legacyx_creditos_get_client_data($client_post_id) {
    $defaults = legacyx_creditos_defaults();
    $saved = get_post_meta($client_post_id, '_legacyx_creditos', true);
    return wp_parse_args(is_array($saved) ? $saved : array(), $defaults);
}

function legacyx_creditos_admin_box() {
    add_meta_box('legacyx_creditos', 'CreditOS — Credit & Capital', 'legacyx_creditos_render_admin_box', 'legacyx_client', 'normal', 'default');
}
add_action('add_meta_boxes', 'legacyx_creditos_admin_box');

function legacyx_creditos_render_admin_box($post) {
    $data = legacyx_creditos_get_client_data($post->ID);
    wp_nonce_field('legacyx_save_creditos', 'legacyx_creditos_nonce');
    $stages = array('Not Started','Intake','Review','Action Plan','Building','Funding Ready','Monitoring');
    echo '<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">';
    foreach (array('personal_stage'=>'Personal Credit Stage','business_stage'=>'Business Credit Stage') as $key=>$label) {
        echo '<p><label style="display:block;font-weight:600;margin-bottom:6px">'.esc_html($label).'</label><select name="legacyx_creditos['.esc_attr($key).']" style="width:100%">';
        foreach ($stages as $stage) echo '<option '.selected($data[$key],$stage,false).'>'.esc_html($stage).'</option>';
        echo '</select></p>';
    }
    echo '<p><label style="display:block;font-weight:600;margin-bottom:6px">Personal Credit Score / Range</label><input style="width:100%" name="legacyx_creditos[personal_score]" value="'.esc_attr($data['personal_score']).'"></p>';
    echo '<p><label style="display:block;font-weight:600;margin-bottom:6px">Business Credit Status</label><input style="width:100%" name="legacyx_creditos[business_status]" value="'.esc_attr($data['business_status']).'"></p>';
    echo '<p><label style="display:block;font-weight:600;margin-bottom:6px">Funding Goal</label><input style="width:100%" name="legacyx_creditos[funding_goal]" value="'.esc_attr($data['funding_goal']).'"></p></div>';
    echo '<p><label style="display:block;font-weight:600;margin-bottom:6px">Next Recommended Action</label><textarea style="width:100%;min-height:80px" name="legacyx_creditos[next_action]">'.esc_textarea($data['next_action']).'</textarea></p>';
}

function legacyx_creditos_save($post_id) {
    if (get_post_type($post_id) !== 'legacyx_client') return;
    if (!isset($_POST['legacyx_creditos_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['legacyx_creditos_nonce'])), 'legacyx_save_creditos')) return;
    if (!current_user_can('edit_post', $post_id) || !isset($_POST['legacyx_creditos']) || !is_array($_POST['legacyx_creditos'])) return;
    $raw = wp_unslash($_POST['legacyx_creditos']);
    $data = legacyx_creditos_get_client_data($post_id);
    foreach (array('personal_stage','business_stage','personal_score','business_status','funding_goal') as $key) if (isset($raw[$key])) $data[$key] = sanitize_text_field($raw[$key]);
    if (isset($raw['next_action'])) $data['next_action'] = sanitize_textarea_field($raw['next_action']);
    update_post_meta($post_id, '_legacyx_creditos', $data);
}
add_action('save_post_legacyx_client', 'legacyx_creditos_save', 20);
