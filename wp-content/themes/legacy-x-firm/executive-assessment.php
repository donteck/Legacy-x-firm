<?php
if (!defined('ABSPATH')) exit;
get_header();
$submitted = isset($_GET['submitted']) && $_GET['submitted'] === '1';
?>
<main class="lx-assessment-page">
<section class="assessment-hero">
  <div class="wrap assessment-hero-grid">
    <div>
      <div class="kicker">Start Here • Executive Assessment</div>
      <h1>Clarify the position.<br><em>Define the next move.</em></h1>
      <p>Tell us where you are, what you are building, and what requires attention. This private assessment gives Legacy X Firm the strategic context needed to organize the right advisory path around your business, credit, capital, financial position, compliance, and long-term objectives.</p>
    </div>
    <aside class="assessment-brief">
      <span>Private Intake</span>
      <strong>One coordinated starting point.</strong>
      <p>Your assessment is reviewed as one strategic picture—not as a collection of isolated services.</p>
      <div class="assessment-meta"><b>01</b> Business & ownership</div>
      <div class="assessment-meta"><b>02</b> Capital & credit</div>
      <div class="assessment-meta"><b>03</b> Financial priorities</div>
      <div class="assessment-meta"><b>04</b> Strategic objectives</div>
    </aside>
  </div>
</section>

<section class="assessment-section">
  <div class="wrap assessment-shell">
    <?php if ($submitted): ?>
      <div class="assessment-success" role="status">
        <div class="kicker">Assessment Received</div>
        <h2>Your strategic intake is now in review.</h2>
        <p>Legacy X Firm has received your assessment. The information will be used to determine the most appropriate next step for your advisory relationship.</p>
        <a class="btn dark" href="<?php echo esc_url(home_url('/')); ?>">Return Home</a>
      </div>
    <?php else: ?>
    <form class="assessment-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
      <input type="hidden" name="action" value="legacyx_submit_assessment">
      <?php wp_nonce_field('legacyx_assessment_submit','legacyx_assessment_nonce'); ?>

      <div class="assessment-form-head">
        <div><div class="kicker">Executive Intake</div><h2>Build your strategic profile.</h2></div>
        <p>Complete the sections below. Required fields are marked with an asterisk.</p>
      </div>

      <fieldset>
        <legend><span>01</span> Principal Information</legend>
        <div class="assessment-grid two">
          <label>Full Name *<input type="text" name="full_name" required autocomplete="name"></label>
          <label>Email Address *<input type="email" name="email" required autocomplete="email"></label>
          <label>Phone Number<input type="tel" name="phone" autocomplete="tel"></label>
          <label>City / State / Country<input type="text" name="location"></label>
        </div>
      </fieldset>

      <fieldset>
        <legend><span>02</span> Ownership & Enterprise</legend>
        <div class="assessment-grid two">
          <label>Primary Role *<select name="role" required><option value="">Select one</option><option>Business Owner</option><option>Founder</option><option>Executive</option><option>Investor</option><option>Real Estate Principal</option><option>Family Enterprise</option><option>Other</option></select></label>
          <label>Business / Organization Name<input type="text" name="business_name"></label>
          <label>Business Stage<select name="business_stage"><option value="">Select one</option><option>Planning / Pre-launch</option><option>Early Stage</option><option>Established</option><option>Growth / Expansion</option><option>Multi-entity / Portfolio</option></select></label>
          <label>Approximate Annual Revenue<select name="revenue"><option value="">Prefer not to say / Not applicable</option><option>Pre-revenue</option><option>Under $100K</option><option>$100K–$500K</option><option>$500K–$1M</option><option>$1M–$5M</option><option>$5M–$25M</option><option>$25M+</option></select></label>
        </div>
      </fieldset>

      <fieldset>
        <legend><span>03</span> Strategic Priorities</legend>
        <p class="field-help">Select every area that currently matters.</p>
        <div class="assessment-checks">
          <label><input type="checkbox" name="priorities[]" value="Credit & Capital"> Credit & Capital</label>
          <label><input type="checkbox" name="priorities[]" value="Business Advisory"> Business Advisory</label>
          <label><input type="checkbox" name="priorities[]" value="Financial Strategy"> Financial Strategy</label>
          <label><input type="checkbox" name="priorities[]" value="Tax & Compliance"> Tax & Compliance</label>
          <label><input type="checkbox" name="priorities[]" value="Grants & Organizations"> Grants & Organizations</label>
          <label><input type="checkbox" name="priorities[]" value="Funding Readiness"> Funding Readiness</label>
          <label><input type="checkbox" name="priorities[]" value="Operations & Systems"> Operations & Systems</label>
          <label><input type="checkbox" name="priorities[]" value="Long-Term / Legacy Planning"> Long-Term / Legacy Planning</label>
        </div>
      </fieldset>

      <fieldset>
        <legend><span>04</span> Current Position</legend>
        <div class="assessment-grid two">
          <label>Capital Need / Objective<select name="capital_need"><option value="">Select one</option><option>No immediate capital need</option><option>Under $50K</option><option>$50K–$250K</option><option>$250K–$1M</option><option>$1M–$5M</option><option>$5M+</option></select></label>
          <label>Timeline *<select name="timeline" required><option value="">Select one</option><option>Immediate</option><option>Within 30 days</option><option>1–3 months</option><option>3–6 months</option><option>6–12 months</option><option>Long-term planning</option></select></label>
        </div>
        <label class="assessment-wide">What is the most important outcome you want to achieve? *<textarea name="objective" rows="5" required></textarea></label>
        <label class="assessment-wide">What is currently preventing or slowing that outcome?<textarea name="obstacle" rows="4"></textarea></label>
      </fieldset>

      <fieldset>
        <legend><span>05</span> Advisory Fit</legend>
        <div class="assessment-grid two">
          <label>Preferred Engagement<select name="engagement"><option value="">Select one</option><option>Strategic Consultation</option><option>Guided Advisory</option><option>Done-for-You Execution</option><option>Ongoing Executive Advisory</option><option>Not sure yet</option></select></label>
          <label>How did you hear about Legacy X Firm?<input type="text" name="source"></label>
        </div>
        <label class="assessment-consent"><input type="checkbox" name="consent" value="1" required> I confirm that the information submitted is accurate to the best of my knowledge and authorize Legacy X Firm to review it for the purpose of evaluating potential services and next steps. *</label>
      </fieldset>

      <div class="assessment-submit">
        <div><b>Ready for review?</b><span>Your information is submitted through the secure WordPress intake handler.</span></div>
        <button class="btn dark" type="submit">Submit Executive Assessment</button>
      </div>
    </form>
    <?php endif; ?>
  </div>
</section>
</main>
<?php get_footer(); ?>
