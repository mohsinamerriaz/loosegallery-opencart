<div id="product" class="blankt_product_modal_main">
  <div class="blankt_modal_close_icon">x</div>
  <?php if ($options) { ?>
  <?php foreach ($options as $option) { ?>

  <?php if($option['type'] == 'select' && !empty($blankt_product_status)) { ?>
  <?php } elseif ($option['type'] == 'select') { ?>

  <div class="form-group text-left <?php echo ($option['required'] ? ' required' : ''); ?>">
    <label class="control-label" for="input-option<?php echo $option['product_option_id']; ?>">
    <?php echo ("Size" == $option['name'])? "Please choose your size and format:" : $option['name']; ?>
    </label>
    <select <?php if ($option['required']) { ?>required<?php } ?> name="option[<?php echo $option['product_option_id']; ?>]" id="input-option<?php echo $option['product_option_id']; ?>" class="form-control option-<?php echo $option['name']; ?>">
      <option value=""><?php echo $text_select; ?></option>
      <?php foreach ($option['product_option_value'] as $option_value) { ?>
      <option value="<?php echo $option_value['product_option_value_id']; ?>"><?php echo $option_value['name']; ?>
      <?php if ($option_value['price']) { ?>
      <?php echo $option_value['price_prefix']; ?><?php echo $option_value['price']; ?>
      <?php } ?>
      </option>
      <?php } ?>
    </select>
  </div>

  <?php echo empty($blankt_add_product_option_html) ? '' : $blankt_add_product_option_html;  ?>

  <?php } ?>
  <?php if ($option['type'] == 'radio') { ?>
  <div class="form-group text-left <?php echo ($option['required'] ? ' required' : ''); ?>">
    <label class="control-label"><?php echo $option['name']; ?></label>
    <div id="input-option<?php echo $option['product_option_id']; ?>">
      <?php foreach ($option['product_option_value'] as $option_value) { ?>
      <div class="radio">
        <label>
          <input type="radio" name="option[<?php echo $option['product_option_id']; ?>]" value="<?php echo $option_value['product_option_value_id']; ?>" />
          <?php echo $option_value['name']; ?>
          <?php if ($option_value['price']) { ?>
          (<?php echo $option_value['price_prefix']; ?><?php echo $option_value['price']; ?>)
          <?php } ?>
        </label>
      </div>
      <?php } ?>
    </div>
  </div>
  <?php } ?>
  <?php if ($option['type'] == 'checkbox') { ?>
  <div class="form-group text-left  <?php echo ($option['required'] ? ' required' : ''); ?>" style="overflow: hidden;">
    <label class="control-label"><?php echo $option['name']; ?></label>
    <div id="input-option<?php echo $option['product_option_id']; ?>">
      <?php foreach ($option['product_option_value'] as $option_value) { ?>
      <label class="col-xs-6" style="font-weight:normal;">
        	<div class="checkbox">
      	<?php if($option_value['image']){?>
      	<img src="<?php echo $option_value['image']; ?>" class="img-thumbnail" />
          <?php } ?>
          <input type="checkbox" name="option[<?php echo $option['product_option_id']; ?>][]" value="<?php echo $option_value['product_option_value_id']; ?>" />
          <?php echo $option_value['name']; ?>
          <?php if ($option_value['price']) { ?>
          (<?php echo $option_value['price_prefix']; ?><?php echo $option_value['price']; ?>)
          <?php } ?>
      	</div>
      </label>
      <?php } ?>
    </div>
  </div>
  <?php } ?>
  <?php if ($option['type'] == 'image') { ?>
  <div class="form-group text-left <?php echo ($option['required'] ? ' required' : ''); ?>">
    <label class="control-label"><?php echo $option['name']; ?></label>
    <div id="input-option<?php echo $option['product_option_id']; ?>">
      <?php foreach ($option['product_option_value'] as $option_value) { ?>
      <div class="radio">
        <label>
          <input type="radio" name="option[<?php echo $option['product_option_id']; ?>]" value="<?php echo $option_value['product_option_value_id']; ?>" />
          <img src="<?php echo $option_value['image']; ?>" alt="<?php echo $option_value['name'] . ($option_value['price'] ? ' ' . $option_value['price_prefix'] . $option_value['price'] : ''); ?>" class="img-thumbnail" /> <?php echo $option_value['name']; ?>
          <?php if ($option_value['price']) { ?>
          (<?php echo $option_value['price_prefix']; ?><?php echo $option_value['price']; ?>)
          <?php } ?>
        </label>
      </div>
      <?php } ?>
    </div>
  </div>
  <?php } ?>

  <?php if ($option['type'] == 'text' && $option['option_id'] == $blank_product_option_id && !$option['required']) { ?>
    <input type="hidden" name="option[<?php echo $option['product_option_id']; ?>]" value="<?php echo $option['value']; ?>" id="input-option<?php echo $option['product_option_id']; ?>" class="form-control" />
  <?php } elseif ($option['type'] == 'text') { ?>

  <div class="form-group text-left <?php echo ($option['required'] ? ' required' : ''); ?>">
    <label class="control-label" for="input-option<?php echo $option['product_option_id']; ?>"><?php echo $option['name']; ?></label>
    <input type="text" name="option[<?php echo $option['product_option_id']; ?>]" value="<?php echo $option['value']; ?>" placeholder="<?php echo $option['name']; ?>" id="input-option<?php echo $option['product_option_id']; ?>" class="form-control" />
  </div>
  <?php } ?>
  <?php if ($option['type'] == 'textarea') { ?>
  <div class="form-group text-left <?php echo ($option['required'] ? ' required' : ''); ?>">
    <label class="control-label" for="input-option<?php echo $option['product_option_id']; ?>"><?php echo $option['name']; ?></label>
    <textarea name="option[<?php echo $option['product_option_id']; ?>]" rows="5" placeholder="<?php echo $option['name']; ?>" id="input-option<?php echo $option['product_option_id']; ?>" class="form-control"><?php echo $option['value']; ?></textarea>
  </div>
  <?php } ?>
  <?php if ($option['type'] == 'file') { ?>
  <div class="form-group text-left <?php echo ($option['required'] ? ' required' : ''); ?>">
    <label class="control-label"><?php echo $option['name']; ?></label>
    <button type="button" id="button-upload<?php echo $option['product_option_id']; ?>" data-loading-text="<?php echo $text_loading; ?>" class="btn btn-default btn-block"><i class="fa fa-upload"></i> <?php echo $button_upload; ?></button>
    <input type="hidden" name="option[<?php echo $option['product_option_id']; ?>]" value="" id="input-option<?php echo $option['product_option_id']; ?>" />
  </div>
  <?php } ?>
  <?php if ($option['type'] == 'date') { ?>
  <div class="form-group text-left <?php echo ($option['required'] ? ' required' : ''); ?>">
    <label class="control-label" for="input-option<?php echo $option['product_option_id']; ?>"><?php echo $option['name']; ?></label>
    <div class="input-group date">
      <input type="text" name="option[<?php echo $option['product_option_id']; ?>]" value="<?php echo $option['value']; ?>" data-date-format="YYYY-MM-DD" id="input-option<?php echo $option['product_option_id']; ?>" class="form-control" />
      <span class="input-group-btn">
      <button class="btn btn-default" type="button"><i class="fa fa-calendar"></i></button>
      </span></div>
  </div>
  <?php } ?>
  <?php if ($option['type'] == 'datetime') { ?>
  <div class="form-group text-left <?php echo ($option['required'] ? ' required' : ''); ?>">
    <label class="control-label" for="input-option<?php echo $option['product_option_id']; ?>"><?php echo $option['name']; ?></label>
    <div class="input-group datetime">
      <input type="text" name="option[<?php echo $option['product_option_id']; ?>]" value="<?php echo $option['value']; ?>" data-date-format="YYYY-MM-DD HH:mm" id="input-option<?php echo $option['product_option_id']; ?>" class="form-control" />
      <span class="input-group-btn">
      <button type="button" class="btn btn-default"><i class="fa fa-calendar"></i></button>
      </span></div>
  </div>
  <?php } ?>
  <?php if ($option['type'] == 'time') { ?>
  <div class="form-group text-left <?php echo ($option['required'] ? ' required' : ''); ?>">
    <label class="control-label" for="input-option<?php echo $option['product_option_id']; ?>"><?php echo $option['name']; ?></label>
    <div class="input-group time">
      <input type="text" name="option[<?php echo $option['product_option_id']; ?>]" value="<?php echo $option['value']; ?>" data-date-format="HH:mm" id="input-option<?php echo $option['product_option_id']; ?>" class="form-control" />
      <span class="input-group-btn">
      <button type="button" class="btn btn-default"><i class="fa fa-calendar"></i></button>
      </span></div>
  </div>
  <?php } ?>
  <?php } ?>
  <?php } ?>

  <div class="form-group text-left">
    <label class="control-label" for="input-quantity"><?php echo $entry_qty; ?></label>
    <input type="text" name="quantity" value="<?php echo $minimum; ?>" size="2" id="input-quantity" class="form-control" />
    <input type="hidden" name="product_id" value="<?php echo $product_id; ?>" />
  </div>

  <div class="text-right">
    <button class="swal-blankt-confirm-button swal-button swal-button--confirm blankt_bg_blue"><?php echo $text_update_button_cart; ?></button>
  </div>
</div>