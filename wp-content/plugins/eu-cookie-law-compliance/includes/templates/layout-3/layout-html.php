<div class="tplis-cl-cookies<?php echo $this->is_btn_refuse ? ' tplis-cl-is-btn-refuse' : ''; ?>">
    <div class="tplis-cl-cookies-text">
        <div class="tplis-cl-cookies-head">
            <h4>{cookie_title}</h4>
        </div>
        <div class="tplis-cl-cookies-content-text">
            <div class="tplis-cl-message">{cookie_message}</div>
        </div>
    </div>
    <div class="tplis-cl-cookies-buttons">

        <a class="tplis-cl-button tplis-cl-button-accept" role="button"
           href="#" <?php echo tplis_cl_add_attr_event('click', 'accept'); ?>>{cookie_accept_text}</a>

        <?php if ($this->is_btn_refuse): ?>
            <a class="tplis-cl-button tplis-cl-button-refuse" role="button"
               href="#" <?php echo tplis_cl_add_attr_event('click', 'refuse'); ?>>{cookie_refuse_text}</a>
        <?php endif; ?>
    </div>
</div>