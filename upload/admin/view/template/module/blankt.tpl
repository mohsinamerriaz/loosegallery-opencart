<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-blankt" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid">
    <div class="alert alert-info"><i class="fa fa-info-circle"></i> <?php echo $help_warning; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>

    <?php if ($error_warning) { ?>
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?></h3>
      </div>
      <div class="panel-body">
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-blankt" class="form-horizontal">
          <div class="form-group">
            <label class="col-sm-3 control-label" for="input-status"><?php echo $entry_status; ?></label>
            <div class="col-sm-9">
              <select name="blankt_status" id="input-status" class="form-control">
                <?php if ($blankt_status) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-3 control-label" for="input-blankt_api_key"><?php echo $entry_api_key; ?></label>
            <div class="col-sm-9">
              <input type="text" name="blankt_api_key" value="<?php echo $blankt_api_key; ?>" placeholder="<?php echo $entry_api_key; ?>" id="input-blankt_api_key" class="form-control" />
              <?php if (!empty($error_blankt_api_key)) { ?>
              <div class="text-danger"><?php echo $error_blankt_api_key; ?></div>
              <?php } ?>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-3 control-label" for="input-blankt_to_website_redirect_url"><?php echo $entry_blankt_to_website_redirect_url; ?></label>
            <div class="col-sm-9">
              <input type="text" name="blankt_blankt_to_website_redirect_url" value="<?php echo $blankt_blankt_to_website_redirect_url; ?>" placeholder="<?php echo $entry_blankt_to_website_redirect_url; ?>" id="input-blankt_to_website_redirect_url" class="form-control" />
              <?php if (!empty($error_blankt_blankt_to_website_redirect_url)) { ?>
              <div class="text-danger"><?php echo $error_blankt_blankt_to_website_redirect_url; ?></div>
              <?php } ?>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-3 control-label" for="input-website_to_blankt_redirect_url"><?php echo $entry_website_to_blankt_redirect_url; ?></label>
            <div class="col-sm-9">
              <input type="text" name="blankt_website_to_blankt_redirect_url" value="<?php echo $blankt_website_to_blankt_redirect_url; ?>" placeholder="<?php echo $entry_integration_url; ?>" id="input-website_to_blankt_redirect_url" class="form-control" />
              <?php if (!empty($error_blankt_website_to_blankt_redirect_url)) { ?>
              <div class="text-danger"><?php echo $error_blankt_website_to_blankt_redirect_url; ?></div>
              <?php } ?>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-3 control-label" for="input-product_ids"><span data-toggle="tooltip" title="<?php echo $help_product_ids; ?>"><?php echo $entry_product_ids; ?></span></label>
            <div class="col-sm-9">
              <input type="text" name="product_ids" value="" placeholder="<?php echo $entry_product_ids; ?>" id="input-product_ids" class="form-control" />
              <div id="product-product_ids" class="well well-sm" style="height: 150px; overflow: auto;">
                <?php foreach ($product_product_ids as $product_product_id) { ?>
                <div id="product-product_ids<?php echo $product_product_id['product_id']; ?>"><i class="fa fa-minus-circle"></i> <?php echo $product_product_id['name']; ?>
                  <input type="hidden" name="blankt_product_ids[]" value="<?php echo $product_product_id['product_id']; ?>" />
                </div>
                <?php } ?>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-3 control-label" for="input-blankt_terms_and_condtions"><span data-toggle="tooltip" title="<?php echo $help_blankt_terms_and_condtions; ?>"><?php echo $entry_blankt_terms_and_condtions; ?></span></label>
            <div class="col-sm-9">
              <textarea name="blankt_terms_and_condtions" placeholder="<?php echo $entry_blankt_terms_and_condtions; ?>" id="input-blankt_terms_and_condtions"><?php echo $blankt_terms_and_condtions; ?></textarea>
            </div>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>


<script type="text/javascript"><!--
  $(document).ready(function () {
    $('#input-blankt_terms_and_condtions').summernote({height: 300});
  })
  // Products
  $('input[name=\'product_ids\']').autocomplete({
    'source': function(request, response) {
      $.ajax({
        url: 'index.php?route=catalog/product/autocomplete&token=<?php echo $token; ?>&filter_name=' +  encodeURIComponent(request),
        dataType: 'json',
        success: function(json) {
          response($.map(json, function(item) {
            return {
              label: item['name'],
              value: item['product_id']
            }
          }));
        }
      });
    },
    'select': function(item) {
      $('input[name=\'product_ids\']').val('');

      $('#product-product_ids' + item['value']).remove();

      $('#product-product_ids').append('<div id="product-product_ids' + item['value'] + '"><i class="fa fa-minus-circle"></i> ' + item['label'] + '<input type="hidden" name="blankt_product_ids[]" value="' + item['value'] + '" /></div>');
    }
  });

  $('#product-product_ids').delegate('.fa-minus-circle', 'click', function() {
    $(this).parent().remove();
  });
//--></script>

<?php echo $footer; ?>