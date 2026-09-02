<?php
if(!defined('ABSPATH'))exit;
function legacyx_register_roles_permissions(){
$staff_caps=array('read'=>true,'legacyx_access_firm_os'=>true,'legacyx_manage_clients'=>true,'legacyx_manage_engagements'=>true,'legacyx_manage_communications'=>true);
add_role('legacyx_advisor','Legacy X Advisor',$staff_caps);
add_role('legacyx_manager','Legacy X Executive Manager',array_merge($staff_caps,array('legacyx_manage_applications'=>true,'legacyx_manage_billing'=>true,'legacyx_view_reports'=>true)));
add_role('legacyx_client','Legacy X Client',array('read'=>true,'legacyx_access_client_portal'=>true));
$admin=get_role('administrator');if($admin){foreach(array('legacyx_access_firm_os','legacyx_manage_clients','legacyx_manage_engagements','legacyx_manage_communications','legacyx_manage_applications','legacyx_manage_billing','legacyx_view_reports','legacyx_access_client_portal') as $cap)$admin->add_cap($cap);}
}add_action('init','legacyx_register_roles_permissions',5);
function legacyx_is_staff_user($user_id=0){$u=$user_id?get_userdata($user_id):wp_get_current_user();return $u&&($u->has_cap('manage_options')||$u->has_cap('legacyx_access_firm_os'));}
function legacyx_is_client_user($user_id=0){$u=$user_id?get_userdata($user_id):wp_get_current_user();return $u&&($u->has_cap('legacyx_access_client_portal')||legacyx_find_client_by_wp_user($u->ID));}
function legacyx_protect_client_pages(){if(is_admin()||wp_doing_ajax())return;$protected=array('client-portal','my-services','credit-capital-center','business-center','documents-tasks','applications-center','billing-payments','appointments-messages');foreach($protected as $slug){if(is_page($slug)&&!is_user_logged_in()){wp_safe_redirect(wp_login_url(home_url('/'.$slug.'/')));exit;}}}add_action('template_redirect','legacyx_protect_client_pages',1);
function legacyx_client_admin_restrictions(){if(!is_user_logged_in())return;$u=wp_get_current_user();if(in_array('legacyx_client',(array)$u->roles,true)&&is_admin()&&!wp_doing_ajax()){wp_safe_redirect(home_url('/client-portal/'));exit;}}add_action('admin_init','legacyx_client_admin_restrictions');
function legacyx_client_hide_admin_bar($show){$u=wp_get_current_user();if($u&&in_array('legacyx_client',(array)$u->roles,true))return false;return $show;}add_filter('show_admin_bar','legacyx_client_hide_admin_bar');
function legacyx_staff_menu_security(){if(current_user_can('manage_options'))return;if(current_user_can('legacyx_access_firm_os')){remove_menu_page('tools.php');remove_menu_page('edit-comments.php');remove_menu_page('themes.php');remove_menu_page('plugins.php');remove_menu_page('options-general.php');remove_menu_page('users.php');}}add_action('admin_menu','legacyx_staff_menu_security',999);
