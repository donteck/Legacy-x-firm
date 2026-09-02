<?php
if(!defined('ABSPATH'))exit;

/**
 * Phase 14 role foundation.
 * Capability installation is versioned so normal page loads do not mutate roles.
 */
function legacyx_phase14_capability_map(){
    return array(
        'legacyx_advisor'=>array(
            'read'=>true,
            'legacyx_access_firm_os'=>true,
            'legacyx_manage_clients'=>true,
            'legacyx_manage_engagements'=>true,
            'legacyx_manage_communications'=>true,
            'legacyx_manage_workflow'=>true,
        ),
        'legacyx_manager'=>array(
            'read'=>true,
            'legacyx_access_firm_os'=>true,
            'legacyx_manage_clients'=>true,
            'legacyx_manage_engagements'=>true,
            'legacyx_manage_communications'=>true,
            'legacyx_manage_workflow'=>true,
            'legacyx_manage_applications'=>true,
            'legacyx_manage_billing'=>true,
            'legacyx_view_reports'=>true,
        ),
        'legacyx_client'=>array(
            'read'=>true,
            'legacyx_access_client_portal'=>true,
        ),
    );
}

function legacyx_install_phase14_roles(){
    $version='1.0.0';
    if(get_option('legacyx_roles_version')===$version)return;

    foreach(legacyx_phase14_capability_map() as $role_name=>$caps){
        $label=$role_name==='legacyx_advisor'?'Legacy X Advisor':($role_name==='legacyx_manager'?'Legacy X Executive Manager':'Legacy X Client');
        if(!get_role($role_name))add_role($role_name,$label,array('read'=>true));
        $role=get_role($role_name);
        if($role){foreach($caps as $cap=>$grant)$role->add_cap($cap,(bool)$grant);}
    }

    $admin=get_role('administrator');
    if($admin){
        foreach(array(
            'legacyx_access_firm_os','legacyx_manage_clients','legacyx_manage_engagements',
            'legacyx_manage_communications','legacyx_manage_workflow','legacyx_manage_applications',
            'legacyx_manage_billing','legacyx_view_reports','legacyx_access_client_portal'
        ) as $cap)$admin->add_cap($cap,true);
    }
    update_option('legacyx_roles_version',$version,false);
}
add_action('init','legacyx_install_phase14_roles',5);

function legacyx_is_staff_user($user_id=0){
    $u=$user_id?get_userdata($user_id):wp_get_current_user();
    return $u&&($u->has_cap('manage_options')||$u->has_cap('legacyx_access_firm_os'));
}

function legacyx_is_client_user($user_id=0){
    $u=$user_id?get_userdata($user_id):wp_get_current_user();
    if(!$u||!$u->exists())return false;
    return $u->has_cap('legacyx_access_client_portal')||($u->ID&&legacyx_find_client_by_wp_user($u->ID));
}

function legacyx_protect_client_pages(){
    if(is_admin()||wp_doing_ajax())return;
    $protected=array('client-portal','my-services','credit-capital-center','business-center','documents-tasks','applications-center','billing-payments','appointments-messages');
    foreach($protected as $slug){
        if(!is_page($slug))continue;
        if(!is_user_logged_in()){
            wp_safe_redirect(wp_login_url(home_url('/'.$slug.'/')));
            exit;
        }
        if(!legacyx_is_staff_user()&&!legacyx_is_client_user()){
            wp_safe_redirect(home_url('/client-portal/'));
            exit;
        }
    }
}
add_action('template_redirect','legacyx_protect_client_pages',1);

function legacyx_client_admin_restrictions(){
    if(!is_user_logged_in()||wp_doing_ajax()||wp_doing_cron())return;
    global $pagenow;
    if(in_array($pagenow,array('admin-post.php','async-upload.php'),true))return;
    $u=wp_get_current_user();
    if(in_array('legacyx_client',(array)$u->roles,true)&&is_admin()){
        wp_safe_redirect(home_url('/client-portal/'));
        exit;
    }
}
add_action('admin_init','legacyx_client_admin_restrictions');

function legacyx_client_hide_admin_bar($show){
    $u=wp_get_current_user();
    if($u&&in_array('legacyx_client',(array)$u->roles,true))return false;
    return $show;
}
add_filter('show_admin_bar','legacyx_client_hide_admin_bar');

function legacyx_staff_menu_security(){
    if(current_user_can('manage_options'))return;
    if(current_user_can('legacyx_access_firm_os')){
        foreach(array('tools.php','edit-comments.php','themes.php','plugins.php','options-general.php','users.php') as $menu)remove_menu_page($menu);
    }
}
add_action('admin_menu','legacyx_staff_menu_security',999);
