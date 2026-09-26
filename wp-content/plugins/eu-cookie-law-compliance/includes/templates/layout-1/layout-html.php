
<div class="tplis-cl-cookies<?php echo $this->is_btn_refuse ? ' tplis-cl-is-btn-refuse' : ''; ?>">
    <div class="tplis-cl-container tplis-cl-cookies-text">
        <h4>{cookie_title}</h4>
        <div class="tplis-cl-message">{cookie_message}</div>
    </div>

    <div class="tplis-cl-cookies-buttons">

        <a class="tplis-cl-row tplis-cl-button-accept" role="button" href="#" <?php echo tplis_cl_add_attr_event( 'click', 'accept' ); ?>>
            <div class="tplis-cl-button-image">
				<svg class="tplis-cl-img-btn" width="30px" height="20px" >
				<g>
				<polygon points="10.756,20.395 9.623,20.395 0.002,10.774 1.136,9.641 10.19,18.694 28.861,0.02 29.998,1.153 	"/>
				</g>
				</svg>
                <p>{cookie_accept_text}</p>
            </div>
        </a>
		<?php if ( $this->is_btn_refuse ): ?>
			<a class="tplis-cl-row tplis-cl-button-refuse" role="button" href="#" <?php echo tplis_cl_add_attr_event( 'click', 'refuse' ); ?>>
				<div class="tplis-cl-button-image">
					<p>{cookie_refuse_text}</p>
					<svg class="tplis-cl-img-btn" width="30px" height="20px" >
					<g>
					<rect x="-3.425" y="9.508" transform="matrix(-0.7097 -0.7045 0.7045 -0.7097 10.1328 24.6695)" width="27.149" height="1.478"/>
					</g>
					<g>
					<rect x="9.45" y="-3.418" transform="matrix(-0.7097 -0.7045 0.7045 -0.7097 10.2333 24.6315)" width="1.483" height="27.251"/>
					</g>
					</svg>
				</div>
			</a>
		<?php endif; ?>
    </div>
</div>
