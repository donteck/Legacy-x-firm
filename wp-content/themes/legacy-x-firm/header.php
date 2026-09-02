<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<header class="lx-header">
  <div class="wrap lx-nav">
    <a class="lx-brand" href="<?php echo esc_url(home_url('/')); ?>"><span class="lx-mark">X</span><span>LEGACY X FIRM</span></a>
    <nav class="lx-links" id="lx-nav" aria-label="Primary navigation">
      <a href="<?php echo esc_url(home_url('/#home')); ?>">Home</a>
      <a href="<?php echo esc_url(home_url('/#advisory')); ?>">Advisory</a>
      <a href="<?php echo esc_url(home_url('/#private')); ?>">Strategy</a>
      <a href="<?php echo esc_url(home_url('/#who')); ?>">Who We Serve</a>
      <a href="<?php echo esc_url(home_url('/#process')); ?>">Process</a>
      <a href="<?php echo esc_url(home_url('/#os')); ?>">Firm OS</a>
      <a class="lx-portal-link" href="<?php echo esc_url(home_url('/client-portal/')); ?>">Client Portal</a>
    </nav>
    <button class="lx-menu" type="button" aria-label="Open navigation" aria-controls="lx-nav" aria-expanded="false">☰</button>
  </div>
</header>
<script>document.addEventListener('DOMContentLoaded',function(){const b=document.querySelector('.lx-menu'),n=document.getElementById('lx-nav');if(!b||!n)return;b.addEventListener('click',function(){const o=n.classList.toggle('open');b.setAttribute('aria-expanded',o?'true':'false')});n.querySelectorAll('a').forEach(a=>a.addEventListener('click',()=>{n.classList.remove('open');b.setAttribute('aria-expanded','false')}));});</script>
