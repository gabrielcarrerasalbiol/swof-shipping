<!--suppress CssUnusedSymbol -->
<style>
    #mainform .form-table, #mainform p.submit {
        display: none;
    }
    .trs-global-method-notice {
        max-width: 750px;
        font-size: 15px;
    }
    .trs-global-method-button {
        font-size: 15px !important;
    }
</style>

<br>

<p class="trs-global-method-notice">
    Here you define a global shipping method – a method that is not tied to a specific shipping zone.
</p>
<p class="trs-global-method-notice">
    You can go with the global or zone-specific methods or both, but if unsure, start with shipping zones.
</p>
<p class="trs-global-method-notice">
    Find more details about how shipping works in WooCommerce in the following
    <a target="_blank" href="<?= esc_html('https://docs.woocommerce.com/document/setting-up-shipping-zones/') ?>">documentation article</a>.
</p>

<br>

<a class="button trs-global-method-button" href="<?= esc_html(admin_url('admin.php?page=wc-settings&tab=shipping')) ?>">Go to shipping zones</a>&nbsp;&nbsp;&nbsp;
<a class="button-primary trs-global-method-button" id="trs_proceed_with_global" href="#">Set up global shipping rules</a>

<script>
    ($ => {
        $('#trs_proceed_with_global').attr('href', document.location.href+'&trs_global');
    })(jQuery)
</script>