<?php defined('C5_EXECUTE') or die(_("Access Denied."));
$dh = Loader::helper('concrete/dashboard');
$ih = Loader::helper('concrete/interface');

$heading = 'Widgets in Category: ' . htmlentities($category['name']);
?>

<?php echo $dh->getDashboardPaneHeaderWrapper($heading); ?>

	<table id="boilerplate_crud_list_table" class="table table-striped">
		<thead>
			<tr>
				<th>Name</th>
				<th>Is Something?</th>
				<th>Rating</th>
				<th>&nbsp;</th>
				<th>&nbsp;</th>
			</tr>
		</thead>
		
		<tbody>
			<?php foreach ($widgets as $widget): ?>
			<tr>
				<td><?php echo htmlentities($widget['name']); ?></td>
				<td><?php echo $widget['isSomething'] ? 'Yes' : 'No'; ?></td>
				<td><?php echo $widget['rating']; ?></td>
				<td><?php echo $ih->button('Edit', $this->action('widget_edit', $widget['id']), false); ?></td>
				<td><?php echo $ih->button('Delete', $this->action('widget_delete', $widget['id']), false); ?></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	
	<p>
		<?php echo $ih->button('&laquo; Go Back', $this->action('category_list'), false); ?>
		&nbsp;&nbsp;&nbsp;
		<?php echo $ih->button('Add New...', $this->action('widget_add', $category['id']), false, 'primary'); ?>
	</p>
	
<?php echo $dh->getDashboardPaneFooterWrapper(); ?>
