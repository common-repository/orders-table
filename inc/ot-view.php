<?php
$options = get_option('ot_main_options');

if (isset($_POST) && !empty($_POST)) {	
	$add = new ot_add_new($_POST);
	$add ->first_step();
}
?>

<div class="ot">
	<?php
	if (isset($options['form-enabled']) && $options['form-enabled'] == 1) {
		if (isset($options['guest-add']) && $options['guest-add'] == 1 || is_user_logged_in()) {
				?>
				<form method="post"  class="add">
					<label><?php echo __('Title:', 'orders-table'); ?>
						<input type="text" placeholder="<?php echo __('Enter a title', 'orders-table'); ?>" name="title" required>
					</label>

					<?php if (isset($options['order-category']) && $options['order-category'] == 1) { ?>
					<label><?php echo __('What do we order?', 'orders-table'); ?>
						<?php 
						wp_dropdown_categories( array(
							'taxonomy' => 'orders-category',
							'name' => 'orders-category',	
							'order' => 'DESC',		
							'orderby' => 'name',				
							'hide_empty' => 0
						) ); 
						?>
					</label>
					<?php } ?>
					
					<?php if (isset($options['order-description']) && $options['order-description'] == 1) { ?>
					<label><?php echo __('Content:', 'orders-table'); ?>
						<textarea name="content" placeholder="<?php echo __('Enter a order content', 'orders-table'); ?>" required></textarea>
					</label>
					<?php } ?>
				
					<?php  
					if (isset($options['extra']) && count($options['extra']) > 0) {
						for ($i = 1; $i <= count($options['extra']); $i++) {
							$name = $options['extra']['extra-field-' . $i]['field-name'];
							$type = $options['extra']['extra-field-' . $i]['field-type'];
							$front = $options['extra']['extra-field-' . $i]['field-front'];
							$required = $options['extra']['extra-field-' . $i]['field-required'] == 1?'required':null;						
							?>
							<label><?php echo $front ?>
								<input type="hidden" name="extra_fields_nonce" value="<?php echo wp_create_nonce(__FILE__); ?>" />
								<input type="<?php echo $type ?>" placeholder="<?php echo $front ?>" name="extra[extra-filed-<?php echo $i ?>]" <?php echo $required ?>/>
							</label>
						<?php 
						} 
					}
					?>
					
					<input type="submit" class="submit" value="<?php echo __('Add', 'orders-table'); ?>">
				</form>
				<?php 
		}
	}
	?>

	<?php
	$args = array( 
		'post_type' => 'orders-table', 
		'posts_per_page' => isset($options['nubmer-orders']) && !empty($options['nubmer-orders'])?$options['nubmer-orders']:10,
		'paged' => isset($_GET['page_orders'])?$_GET['page_orders']:1
	);

	$ot = new WP_Query($args);
	if ($ot->have_posts()) {
	?>
	<table class="ot-post">
		<tr>
			<?php if (isset($options['order-status']) && $options['order-status'] == 1) { ?>
			<th class="status"><?php echo '<img src="' . plugins_url('add/images/status.png', dirname(__FILE__)) . '"> '; ?></th>
			<?php } ?>
			
			<th class="title"><?php echo __( 'Title', 'orders-table' ); ?></th>
			
			<?php if (isset($options['order-description']) && $options['order-description'] == 1) { ?>
			<th class="content"><?php echo __( 'Order content', 'orders-table' ); ?></th>
			<?php } ?>
			
			<?php if (isset($options['order-category']) && $options['order-category'] == 1) { ?>
			<th class="category"><?php echo __( 'Category', 'orders-table' ); ?></th>
			<?php } ?>
			
			<?php if (isset($options['order-author']) && $options['order-author'] == 1) { ?>
			<th class="author"><?php echo __( 'Author', 'orders-table' ); ?></th>
			<?php } ?>
			
			<?php  
			if (isset($options['extra'])) {
				for ($i = 1; $i <= count($options['extra']); $i++) {
					$front = $options['extra']['extra-field-' . $i]['field-front'];					
					?>
					<th class="<?php echo 'extra-field-' . $i ?>"><?php echo $front ?></th>
					<?php
				}
			} 
			?>
		</tr>
		<?php while ($ot->have_posts()):$ot->the_post(); ?>
		<tr>
			<?php if (isset($options['order-status']) && $options['order-status'] == 1) { ?>
			<td>
			<?php 
			$post_status = get_post_status(); 
			if($post_status == 'publish'){ 
				echo  '<img  src="' . plugins_url('add/images/ok.png', dirname(__FILE__)) . '" title="' . __('Success', 'orders-table') . '" alt="' . __('Order status', 'orders-table') . '">';}  
			else if($post_status == 'av'){  
				echo  '<img src="' . plugins_url('add/images/time.png', dirname(__FILE__)) . '" title="' . __( 'Awaiting Verification', 'orders-table' ) . '" alt="' . __('Order status', 'orders-table') . '">';
			}  
			?>
			</td>
			<?php } ?>
			
			<td><?php the_title(); ?></td>
			
			<?php if (isset($options['order-description']) && $options['order-description'] == 1) { ?>
			<td><?php echo str_replace(']]>', ']]>', get_the_content()); ?></td>
			<?php } ?>
			
			<?php if (isset($options['order-category']) && $options['order-category'] == 1) { ?>
			<td><?php 
			$cur_terms = get_the_terms($ot->ID, 'orders-category'); 
			echo isset($cur_terms[0]->name)?$cur_terms[0]->name:null; 
			?></td> 
			<?php } ?>
			
			<?php if (isset($options['order-author']) && $options['order-author'] == 1) { ?>
			<td><?php
			if (get_the_author()) {
				echo get_the_author(); 
			} else {
				echo __('Guest', 'orders-table'); 
			} 
			?></td>
			<?php } ?>
			
			<?php  
			if (isset($options['extra']) && count($options['extra']) > 0) {
				for ($i = 1; $i <= count($options['extra']); $i++) {				
					?>
					<td><?php
					$extra = get_post_custom($ot->ID);
					if (isset($extra['extra-filed-'. $i])) {
						echo $extra['extra-filed-'. $i][0];
					}
					?></td>
					<?php
				}
			} 
			?>
		</tr>
	   <?php endwhile; ?>
	</table>
	<?php 
	} else {
		echo '<h3>' . __('No orders yet ...', 'orders-table') . '</h3>';
	}
	?>

	<div class="ot-pagination"> 
		<?php
		$args = array(
			'base'         => '%_%',
			'format' => '?page_orders=%#%',
			'total'        => $ot->max_num_pages,
			'current'      => $ot->query['paged'],
			'prev_text'    => __('« Previous', 'orders-table'),
			'next_text'    => __('Next »', 'orders-table')
		); 

		echo str_replace( array('href=""', "href=''"), 'href="."', paginate_links($args));
		?>
	</div>
</div>