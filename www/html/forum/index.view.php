<?php $this->load->view('common/header.include.php'); ?>

	Forum Index!


	<table>
		<thead>
			<tr>
				<th>-</th>
				<th>Forum</th>
				<th>Threads</th>
				<th>Last Post</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($forums as $forum): ?>
			<tr>
				<td>-</td>
				<td>
					<?php echo anchor($forum->uri(), $forum->title()); ?>
					<?php echo $forum->description(); ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

<?php $this->load->view('common/footer.include.php'); ?>