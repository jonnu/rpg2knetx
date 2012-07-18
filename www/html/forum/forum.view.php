<?php $this->load->view('common/header.include.php'); ?>
	
	<!-- Breadcrumb -->
	<h6>Forum &rarr; <?php echo $forum->title(); ?></h6>
	<!-- End Breadcrumb -->

	list of threads.

	<?php echo anchor($forum->uri() . '/create', 'Create Thread'); ?>

	<table>
		<thead>
			<tr>
				<th></th>
				<th>Title</th>
				<th>Posts</th>
				<th>Latest Post</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>-</td>
				<td>
					Thread Title [ 1 2 3 ... 24 25 26 ]
				</td>
				<td>n</td>
				<td>x</td>
			</tr>
		</tbody>
	</table>

	<?php echo anchor($forum->uri() . '/create', 'Create Thread'); ?>

<?php $this->load->view('common/footer.include.php'); ?>