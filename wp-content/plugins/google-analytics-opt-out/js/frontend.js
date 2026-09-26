(function () {
    "use strict";

    var $gaoop_checkbox = document.querySelector('.gaoop-checkbox');

    if (!$gaoop_checkbox || $gaoop_checkbox.length <= 0) {
        return;
    }

    var $gaoop = document.querySelector('.gaoop');
    var disable_str = window.disableStr || window.gaoop_disable_str;

    function gaoop_to_infobox() {
        $gaoop_checkbox.checked = true;
    }

    var opted_out = document.cookie.indexOf(disable_str + '=true') >= 0;
    var closed_box = document.cookie.indexOf('gaoop_hide_info=true') >= 0;
    var hide_after_close = 1 === parseInt($gaoop.dataset.gaoop_hide_after_close);

    if (!opted_out && !closed_box) {
        /* Full banner */
        $gaoop.classList.remove('gaoop-hidden');
    } else if (!hide_after_close && (opted_out || closed_box)) {
        /* info box only */
        $gaoop.classList.remove('gaoop-hidden');
        gaoop_to_infobox();
    } else if (hide_after_close && (opted_out || closed_box)) {
        /* fully hidden */
        $gaoop.classList.add('gaoop-hidden');
    }

    /**
     * Click Opt-Out Button
     */
    var $opt_out_button = $gaoop.querySelector('.gaoo-opt-out');
    if ($opt_out_button) {
        $opt_out_button.addEventListener('click', function () {
            if (hide_after_close) {
                $gaoop.classList.add('gaoop-hidden');
            } else {
                gaoop_to_infobox();
            }
        });
    }


    /**
     * Click info icon
     */
    var $info_button = $gaoop.querySelector('.gaoop-info-icon');
    if ($info_button) {
        $info_button.addEventListener('click', function () {
            /* destroy cookie */
            document.cookie = 'gaoop_hide_info=true; expires=Thu, 31 Dec 1901 23:59:59 UTC; SameSite=Strict; path=/';
        });
    }


    /**
     * Keep the banner closed
     */
    var $checkbox = document.querySelector('#gaoop_checkbox');
    if ($checkbox) {
        $checkbox.addEventListener('change', function () {
            if (this.checked) {
                document.cookie = 'gaoop_hide_info=true; expires=Thu, 31 Dec 2099 23:59:59 UTC; SameSite=Strict; path=/';

                if (hide_after_close) {
                    $gaoop.classList.add('gaoop-hidden');
                }
            } else {
                document.cookie = 'gaoop_hide_info=false; expires=Thu, 31 Dec 2000 23:59:59 UTC; SameSite=Strict; path=/';
                $gaoop.classList.remove('gaoop-hidden');
            }
        });
    }
})();
