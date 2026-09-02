<?php
if (!defined('ABSPATH')) exit;
get_header();
$current_user = wp_get_current_user();
$portal_url = home_url('/client-portal/');
?>
<main id="legacyx-client-portal" class="legacyx-portal"><section><div class="legacyx-portal-shell"><div class="legacyx-portal-brand"><span class="kicker">LEGACY X FIRM</span><h1>Client Portal</h1><p>Secure access to your Legacy X Firm account, services, documents and support.</p></div>
<?php if (!is_user_logged_in()) : ?>
<div class="legacyx-login-card"><h2>Welcome Back</h2><p>Sign in to access your client account.</p><?php wp_login_form(array('echo'=>true,'redirect'=>$portal_url,'form_id'=>'legacyx-loginform','label_username'=>'Email or Username','label_password'=>'Password','label_remember'=>'Remember Me','label_log_in'=>'Sign In','remember'=>true)); ?><div class="legacyx-login-links"><a href="<?php echo esc_url(wp_lostpassword_url($portal_url)); ?>">Forgot your password?</a></div><div class="legacyx-support-note"><strong>Need help accessing your account?</strong><br><a href="mailto:info@legacyxfirm.us">info@legacyxfirm.us</a></div></div>
<?php else : ?>
<div class="legacyx-dashboard-wrap"><div class="legacyx-dashboard-topbar"><div><span class="kicker">CLIENT DASHBOARD</span><h2>Welcome, <?php echo esc_html($current_user->display_name ?: $current_user->user_login); ?></h2><p>Your Legacy X Firm client workspace.</p></div><a class="legacyx-logout-button" href="<?php echo esc_url(wp_logout_url($portal_url)); ?>">Log Out</a></div><div class="legacyx-dashboard-grid">
<article class="legacyx-dashboard-card"><span class="legacyx-card-number">01</span><h3>My Services</h3><p>View the Legacy X Firm services associated with your account.</p><span class="legacyx-coming-soon">Portal module</span></article>
<article class="legacyx-dashboard-card"><span class="legacyx-card-number">02</span><h3>Credit Center</h3><p>Access personal and business credit progress as services are connected.</p><span class="legacyx-coming-soon">Portal module</span></article>
<article class="legacyx-dashboard-card"><span class="legacyx-card-number">03</span><h3>Documents</h3><p>Your secure document center will be available from this workspace.</p><span class="legacyx-coming-soon">Portal module</span></article>
<article class="legacyx-dashboard-card"><span class="legacyx-card-number">04</span><h3>Applications</h3><p>Track applications, onboarding items and service requests.</p><span class="legacyx-coming-soon">Portal module</span></article>
<article class="legacyx-dashboard-card"><span class="legacyx-card-number">05</span><h3>Billing & Payments</h3><p>Billing tools and payment history can be connected here.</p><span class="legacyx-coming-soon">Portal module</span></article>
<article class="legacyx-dashboard-card"><span class="legacyx-card-number">06</span><h3>Support</h3><p>Need assistance? Contact the Legacy X Firm team.</p><a class="legacyx-card-link" href="mailto:info@legacyxfirm.us">Contact Support</a></article>
</div></div><?php endif; ?></div></section></main>
<?php get_footer(); ?>
