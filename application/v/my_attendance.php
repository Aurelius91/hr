	<style type="text/css">
	</style>

	<script type="text/javascript">
		$(function() {
			reset();
			init();
			attendanceKeypress();
			attendanceClick();
			attendanceChange();
		});

		function attendanceChange() {
		}

		function attendanceClick() {
			$('.button-prev').click(function() {
				var page = parseInt('<?= $page; ?>');

				page = page - 1 ;

				if (page <= 0) {
					return;
				}

				filter(page);
			});

			$('.button-next').click(function() {
				var page = parseInt('<?= $page; ?>');
				var maxPage = parseInt('<?= $count_page; ?>');

				page = page + 1 ;

				if (page > maxPage) {
					return;
				}

				filter(page);
			});

			$('.item-upload-button').click(function() {
				$('#attendance-file').val("");

				$('#attendance-month').val("<?= $this_month; ?>");
				$('#attendance-month-container').dropdown('set selected', "<?= $this_month; ?>");

				$('#attendance-year').val("<?= $this_year; ?>");
				$('#attendance-year-container').dropdown('set selected', "<?= $this_year; ?>");


				$('.attendance-upload-modal').modal({
					inverted: false,
				}).modal('show');
			});

			$('.item-filter-button').click(function() {
				$('#attendance-filter-month').val("<?= $month; ?>");
				$('#attendance-filter-month-container').dropdown('set selected', "<?= $month; ?>");

				$('#attendance-filter-year').val("<?= $year; ?>");
				$('#attendance-filter-year-container').dropdown('set selected', "<?= $year; ?>");

				$('.attendance-filter-modal').modal({
					inverted: false,
				}).modal('show');
			});

			$('.open-modal-warning-delete').click(function() {
				var attendanceId = $(this).attr('data-attendance-id');
				var attendanceName = $(this).attr('data-attendance-name');
				var attendanceUpdated = $(this).attr('data-attendance-updated');

				$('.delete-attendance-title').html('Delete attendance ' + attendanceName);
				$('.delete-attendance-button').attr('data-attendance-id', attendanceId);
				$('.delete-attendance-button').attr('data-attendance-updated', attendanceUpdated);

				$('.ui.basic.modal.modal-warning-delete').modal('show');
			});
		}

		function attendanceKeypress() {
			$('.input-search').keypress(function(e) {
				if (e.which == 13) {
					var page = 1;

					filter(page);
				}
			});

			$('#input-page').keypress(function(e) {
				if (e.which == 13) {
					var page = $('#input-page').val();

					filter(page);
				}
			});
		}

		function changeFilter(f) {
			filterQuery = f;
		}

		function deleteAttendance() {
			var attendanceId = $('.delete-attendance-button').attr('data-attendance-id');
			var attendanceUpdated = $('.delete-attendance-button').attr('data-attendance-updated');

			$('.ui.basic.modal.modal-warning-delete').modal('hide');
			$('.ui.text.loader').html('Connecting to Database...');
			$('.ui.dimmer.all-loader').dimmer('show');

			$.ajax({
				data :{
					updated: attendanceUpdated,
					"<?= $csrf['name'] ?>": "<?= $csrf['hash'] ?>"
				},
				dataType: 'JSON',
				error: function() {
					$('.ui.dimmer.all-loader').dimmer('hide');
					$('.ui.basic.modal.all-error').modal('show');
					$('.all-error-text').html('Server Error.');
				},
				success: function(data){
					if (data.status == 'success') {
						$('.ui.text.loader').html('Redirecting...');

						window.location.reload();
					}
					else {
						$('.ui.dimmer.all-loader').dimmer('hide');
						$('.ui.basic.modal.all-error').modal('show');
						$('.all-error-text').html(data.message);
					}
				},
				type : 'POST',
				url : '<?= base_url() ?>attendance/ajax_delete/'+ attendanceId +'/',
				xhr: function() {
					var percentage = 0;
					var xhr = new window.XMLHttpRequest();

					xhr.upload.addEventListener('progress', function(evt) {
						$('.ui.text.loader').html('Validating Data..');
					}, false);

					xhr.addEventListener('progress', function(evt) {
						$('.ui.text.loader').html('Delete Data from Database...');
					}, false);

					return xhr;
				},
			});
		}

		function filter(page) {
			var month = $('#attendance-filter-month').val();
			var year = $('#attendance-filter-year').val();

			window.location.href = '<?= base_url(); ?>attendance/my_attendance/'+ page +'/'+ month +'/'+ year +'/';
		}

		function init() {
			$('.dropdown-search, .dropdown-filter').dropdown({
				allowAdditions: true
			});

			$('.ui.search.dropdown.form-input').dropdown('clear');

			$('table').tablesort();
		}

		function reset() {
			$('#input-page').val("<?= $page; ?>");

			<? foreach ($arr_attendance as $attendance): ?>
				<? if ($attendance->status == 'Active'): ?>
					$('#checkbox-attendance-<?= $attendance->id; ?>').attr('checked', true);
				<? else: ?>
					$('#checkbox-attendance-<?= $attendance->id; ?>').attr('checked', false);
				<? endif; ?>
			<? endforeach; ?>
		}

		function uploadFile() {
			$('.attendance-upload-modal').modal('hide');
			$('.ui.text.loader').html('Connecting to Database...');
			$('.ui.dimmer.all-loader').dimmer('show');

			var file_data = $('#attendance-file').prop('files')[0];
			var form_data = new FormData();
			form_data.append('file', file_data);
			form_data.append("<?= $csrf['name'] ?>", "<?= $csrf['hash'] ?>");

			var month = $('#attendance-month').val();
			var year = $('#attendance-year').val();

			$('#loading').modal('show');

			$.ajax({
				cache: false,
				contentType: false,
				data: form_data,
				dataType: 'JSON',
				error: function() {
					$('.ui.dimmer.all-loader').dimmer('hide');
					$('.ui.basic.modal.all-error').modal('show');
					$('.all-error-text').html('Server Error.');
				},
				processData: false,
				type: 'post',
				success: function(data) {
					if (data.status == 'success') {
						$('.ui.text.loader').html('Redirecting...');

						window.location.reload();
					}
					else {
						$('.ui.dimmer.all-loader').dimmer('hide');
						$('.color-red.warning').html(data.message);

						$('.add-muse-image-modal').modal({
							inverted: true,
						}).modal('show');
					}
				},
				url: '<?= base_url(); ?>attendance/ajax_upload/'+ month + '/'+ year +'/',
				xhr: function() {
					var percentage = 0;
					var xhr = new window.XMLHttpRequest();

					xhr.upload.addEventListener('progress', function(evt) {
						$('.ui.text.loader').html('Checking Data..');
					}, false);

					xhr.addEventListener('progress', function(evt) {
						$('.ui.text.loader').html('Updating Database...');
					}, false);

					return xhr;
				}
			});
		}
	</script>

	<!-- Dashboard Here -->
	<div class="ui basic modal modal-warning-delete">
		<div class="ui icon header">
			<i class="trash outline icon delete-icon"></i>
			<span class="delete-attendance-title">Delete Attendance</span>
		</div>
		<div class="content text-center">
			<p>You're about to delete this attendance. You will not be able to undo this action. Are you sure?</p>
		</div>
		<div class="actions">
			<div class="ui red basic cancel inverted button">
				<i class="remove icon"></i>
				No
			</div>
			<div class="ui green ok inverted button delete-attendance-button" onclick="deleteAttendance();">
				<i class="checkmark icon"></i>
				Yes
			</div>
		</div>
	</div>

	<div class="ui modal attendance-filter-modal">
		<i class="close icon"></i>
		<div class="header">Filter Attendance</div>
		<div class="form-content content">
			<div class="form-add">
				<div class="form-content">
					<div class="ui form">
						<div class="two fields">
							<div class="field">
								<label>Month</label>
								<div id="attendance-filter-month-container" class="ui search selection dropdown form-input">
									<input id="attendance-filter-month" type="hidden" class="data-important">
									<i class="dropdown icon"></i>
									<div class="default text">-- Select Month --</div>
									<div class="menu">
										<div class="item" data-value="01">January</div>
										<div class="item" data-value="02">February</div>
										<div class="item" data-value="03">March</div>
										<div class="item" data-value="04">April</div>
										<div class="item" data-value="05">May</div>
										<div class="item" data-value="06">June</div>
										<div class="item" data-value="07">July</div>
										<div class="item" data-value="08">August</div>
										<div class="item" data-value="09">September</div>
										<div class="item" data-value="10">October</div>
										<div class="item" data-value="11">November</div>
										<div class="item" data-value="12">December</div>
									</div>
								</div>
							</div>
							<div class="field">
								<label>Year</label>
								<div id="attendance-filter-year-container" class="ui search selection dropdown form-input">
									<input id="attendance-filter-year" type="hidden" class="data-important">
									<i class="dropdown icon"></i>
									<div class="default text">-- Select year --</div>
									<div class="menu">
										<? for ($i = $start_year; $i <= date('Y', time()); $i++): ?>
											<div class="item" data-value="<?= $i; ?>"><?= $i; ?></div>
										<? endfor; ?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="actions text-right">
				<div class="ui deny button form-button">Exit</div>
				<div class="ui button form-button" onclick="filter(1);">Submit</div>
			</div>
		</div>
	</div>

	<div class="main-content">
		<div class="ui top attached menu table-menu">
			<div class="item item-add-button">
				Employee - Attendance Lists
			</div>
			<div class="item item-add-button">
				<?= $month_view; ?>
			</div>
			<a class="item item-filter-button">
				<i class="upload icon"></i> Filter Attendance
			</a>
		</div>
		<div class="ui bottom attached segment table-segment">
			<table class="ui striped selectable sortable celled table">
				<thead>
					<tr>
						<th class="td-icon">Action</th>
						<th>Name</th>
						<th>Date</th>
						<th>In</th>
						<th>Out</th>
						<th>Status</th>
						<th>Count Late</th>
					</tr>
				</thead>
				<tbody>
					<? if (count($arr_attendance) <= 0): ?>
						<tr>
							<td colspan="10">No Result Founds</td>
						</tr>
					<? else: ?>
						<? foreach ($arr_attendance as $attendance): ?>
							<tr>
								<td class="td-icon">
									<? if ($attendance->imported <= 0): ?>
										<? if (isset($acl['attendance']) && $acl['attendance']->edit > 0): ?>
											<a href="<?= base_url(); ?>attendance/edit/<?= $attendance->id; ?>/">
												<span class="table-icon" data-content="Edit attendance">
													<i class="edit icon"></i>
												</span>
											</a>
										<? endif; ?>

										<? if (isset($acl['attendance']) && $acl['attendance']->delete > 0): ?>
											<a class="open-modal-warning-delete" data-attendance-id="<?= $attendance->id; ?>" data-attendance-name="<?= $attendance->name; ?>" data-attendance-updated="<?= $attendance->updated; ?>">
												<span class="table-icon" data-content="Delete attendance">
													<i class="trash outline icon"></i>
												</span>
											</a>
										<? endif; ?>
									<? endif; ?>
								</td>
								<td><?= $attendance->user_name; ?></td>
								<td><?= $attendance->date_display; ?></td>
								<td><?= $attendance->in; ?></td>
								<td><?= $attendance->out; ?></td>
								<td><?= $attendance->status; ?></td>
								<td><?= $attendance->count_late; ?></td>
							</tr>
						<? endforeach; ?>
					<? endif; ?>
				</tbody>
				<tfoot>
					<tr>
						<th colspan="10">
							<button class="ui button button-prev">Prev</button>
							<span>
								<div class="ui input input-page">
									<input id="input-page" placeholder="" type="text">
								</div> / <?= $count_page; ?>
							</span>
							<button class="ui button button-next">Next</button>
						</th>
					</tr>
				</tfoot>
			</table>
		</div>
	</div>
</body>
</html>