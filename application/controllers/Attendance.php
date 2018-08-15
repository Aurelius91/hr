<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Attendance extends CI_Controller
{
	private $_setting;
	private $_user;
	private $_acl;
	private $_has_image;

	public function __construct()
	{
		parent:: __construct();

		$user_id = $this->session->userdata('user_id');

		if ($user_id > 0)
		{
			$this->_user = $this->core_model->get('user', $user_id);
			$this->_setting = $this->setting_model->load();
			$this->_acl = $this->cms_function->generate_acl($this->_user->id);

			$this->_user->address = $this->cms_function->trim_text($this->_user->address);
			$this->_setting->company_address = $this->cms_function->trim_text($this->_setting->company_address);
			$this->_user->image_name = $this->cms_function->generate_image('user', $this->_user->id);

			$this->_has_image = 0;
		}
		else
		{
			redirect(base_url() . 'login/');
		}
	}




	public function add()
	{
		$acl = $this->_acl;

		if (!isset($acl['attendance']) || $acl['attendance']->add <= 0)
		{
			redirect(base_url());
		}

		$arr_data['setting'] = $this->_setting;
		$arr_data['account'] = $this->_user;
		$arr_data['acl'] = $acl;
		$arr_data['type'] = 'Attendance';
		$arr_data['csrf'] = $this->cms_function->generate_csrf();
		$arr_data['total_size'] = $this->cms_function->check_memory();
		$arr_data['arr_user'] = $this->_get_user();

		$this->load->view('html', $arr_data);
		$this->load->view('attendance_add', $arr_data);
	}

	public function edit($attendance_id = 0)
	{
		$acl = $this->_acl;

		if ($attendance_id <= 0)
		{
			redirect(base_url());
		}

		if (!isset($acl['attendance']) || $acl['attendance']->edit <= 0)
		{
			redirect(base_url());
		}

		$attendance = $this->core_model->get('attendance', $attendance_id);

		$arr_data['setting'] = $this->_setting;
		$arr_data['account'] = $this->_user;
		$arr_data['acl'] = $acl;
		$arr_data['type'] = 'Attendance';
		$arr_data['attendance'] = $attendance;
		$arr_data['csrf'] = $this->cms_function->generate_csrf();
		$arr_data['total_size'] = $this->cms_function->check_memory();
		$arr_data['arr_user'] = $this->_get_user();

		$this->load->view('html', $arr_data);
		$this->load->view('attendance_edit', $arr_data);
	}

	public function my_attendance($page = 1, $month = 0, $year = 0)
	{
		$acl = $this->_acl;

		// get month and year
		$year = ($year == 0) ? date('Y', time()) : $year;
		$month = ($month == 0) ? date('m', time()) : $month;

		$month_view = date('F Y', strtotime($year . '-' . $month . '-' . $this->_setting->setting__hr_monthly_date_start));

		$month_start = ($month == 01) ? 12 : $month - 1;
		$year_start = ($month == 01) ? $year - 1 : $year;
		$date_start = strtotime($year_start . '-' . $month_start . '-' . $this->_setting->setting__hr_monthly_date_start);
		$date_end = strtotime($year . '-' . $month . '-' . ($this->_setting->setting__hr_monthly_date_start - 1));

		$this->db->where('user_id', $this->_user->id);
		$this->db->where('date >=', $date_start);
		$this->db->where('date <', $date_end);
		$this->db->limit($this->_setting->setting__limit_page, ($page - 1) * $this->_setting->setting__limit_page);
		$this->db->order_by("date DESC");
		$arr_attendance = $this->core_model->get('attendance');

		foreach ($arr_attendance as $attendance)
		{
			$attendance->date_display = date('l, d F Y', $attendance->date);
		}

		// count_attendance
		$this->db->where('user_id', $this->_user->id);
		$this->db->where('date >=', $date_start);
		$this->db->where('date <', $date_end);
		$count_attendance = $this->core_model->count('attendance');
		$count_page = ceil($count_attendance / $this->_setting->setting__limit_page);

		$arr_data['setting'] = $this->_setting;
		$arr_data['account'] = $this->_user;
		$arr_data['acl'] = $acl;
		$arr_data['type'] = 'My Attendance';
		$arr_data['page'] = $page;
		$arr_data['count_page'] = $count_page;
		$arr_data['arr_attendance'] = $arr_attendance;
		$arr_data['csrf'] = $this->cms_function->generate_csrf();
		$arr_data['total_size'] = $this->cms_function->check_memory();
		$arr_data['start_year'] = 2015;
		$arr_data['this_month'] = date('m', time());
		$arr_data['this_year'] = date('Y', time());
		$arr_data['month_view'] = $month_view;
		$arr_data['month'] = $month;
		$arr_data['year'] = $year;

		$this->load->view('html', $arr_data);
		$this->load->view('my_attendance', $arr_data);
	}

	public function view($page = 1, $month = 0, $year = 0, $user_id = 0)
	{
		$acl = $this->_acl;

		if (!isset($acl['attendance']) || $acl['attendance']->list <= 0)
		{
			redirect(base_url());
		}

		// get month and year
		$year = ($year == 0) ? date('Y', time()) : $year;
		$month = ($month == 0) ? date('m', time()) : $month;

		$month_view = date('F Y', strtotime($year . '-' . $month . '-' . $this->_setting->setting__hr_monthly_date_start));

		$month_start = ($month == 01) ? 12 : $month - 1;
		$year_start = ($month == 01) ? $year - 1 : $year;
		$date_start = strtotime($year_start . '-' . $month_start . '-' . $this->_setting->setting__hr_monthly_date_start);
		$date_end = strtotime($year . '-' . $month . '-' . ($this->_setting->setting__hr_monthly_date_start - 1));

		if ($user_id > 0)
		{
			$this->db->where('user_id', $user_id);
		}

		$this->db->where('date >=', $date_start);
		$this->db->where('date <', $date_end);
		$this->db->limit($this->_setting->setting__limit_page, ($page - 1) * $this->_setting->setting__limit_page);
		$this->db->order_by("date DESC");
		$arr_attendance = $this->core_model->get('attendance');

		foreach ($arr_attendance as $attendance)
		{
			$attendance->date_display = date('l, d F Y', $attendance->date);
		}

		// count_attendance
		if ($user_id > 0)
		{
			$this->db->where('user_id', $user_id);
		}

		$this->db->where('date >=', $date_start);
		$this->db->where('date <', $date_end);
		$count_attendance = $this->core_model->count('attendance');
		$count_page = ceil($count_attendance / $this->_setting->setting__limit_page);

		$arr_data['setting'] = $this->_setting;
		$arr_data['account'] = $this->_user;
		$arr_data['acl'] = $acl;
		$arr_data['type'] = 'Attendance';
		$arr_data['page'] = $page;
		$arr_data['count_page'] = $count_page;
		$arr_data['arr_attendance'] = $arr_attendance;
		$arr_data['csrf'] = $this->cms_function->generate_csrf();
		$arr_data['total_size'] = $this->cms_function->check_memory();
		$arr_data['start_year'] = 2015;
		$arr_data['this_month'] = date('m', time());
		$arr_data['this_year'] = date('Y', time());
		$arr_data['month_view'] = $month_view;
		$arr_data['month'] = $month;
		$arr_data['year'] = $year;
		$arr_data['user_id'] = $user_id;
		$arr_data['arr_user'] = $this->_get_user();

		$this->load->view('html', $arr_data);
		$this->load->view('attendance', $arr_data);
	}




	public function ajax_add()
	{
		$json['status'] = 'success';

		try
		{
			$this->db->trans_start();

			if ($this->session->userdata('user_id') != $this->_user->id)
			{
				throw new Exception('Server Error. Please log out first.');
			}

			$acl = $this->_acl;

			if (!isset($acl['attendance']) || $acl['attendance']->add <= 0)
			{
				throw new Exception('You have no access to add attendance. Please contact your administrator.');
			}

			$attendance_record = array();
			$image_id = 0;

			foreach ($_POST as $k => $v)
			{
				if ($k == 'image_id')
				{
					$image_id = $v;
				}
				else
				{
					$attendance_record[$k] = ($k == 'date') ? strtotime($v) : $v;
				}
			}

			$this->_validate_add($attendance_record);

			$attendance_id = $this->core_model->insert('attendance', $attendance_record);
			$attendance_record['id'] = $attendance_id;
			$attendance_record['last_query'] = $this->db->last_query();

			$this->cms_function->system_log($attendance_record['id'], 'add', $attendance_record, array(), 'attendance');

			if ($image_id > 0)
			{
				$this->core_model->update('image', $image_id, array('attendance_id' => $attendance_id));
			}

			$this->db->trans_complete();
		}
		catch (Exception $e)
		{
			$json['message'] = $e->getMessage();
			$json['status'] = 'error';

			if ($json['message'] == '')
			{
				$json['message'] = 'Server error.';
			}
		}

		echo json_encode($json);
	}

	public function ajax_change_status($attendance_id)
	{
		$json['status'] = 'success';

		try
		{
			$this->db->trans_start();

			if ($attendance_id <= 0)
			{
				throw new Exception();
			}

			if ($this->session->userdata('user_id') != $this->_user->id)
			{
				throw new Exception('Server Error. Please log out first.');
			}

			$acl = $this->_acl;

			if (!isset($acl['attendance']) || $acl['attendance']->edit <= 0)
			{
				throw new Exception('You have no access to edit attendance. Please contact your administrator.');
			}

			$old_attendance = $this->core_model->get('attendance', $attendance_id);

			$old_attendance_record = array();

			foreach ($old_attendance as $key => $value)
			{
				$old_attendance_record[$key] = $value;
			}

			$attendance_record = array();

			foreach ($_POST as $k => $v)
			{
				$attendance_record[$k] = ($k == 'date') ? strtotime($v) : $v;
			}

			$this->core_model->update('attendance', $attendance_id, $attendance_record);
			$attendance_record['id'] = $attendance_id;
			$attendance_record['last_query'] = $this->db->last_query();

			$this->cms_function->system_log('status', $attendance_record, $old_attendance_record, 'attendance');

			$this->db->trans_complete();
		}
		catch (Exception $e)
		{
			$json['message'] = $e->getMessage();
			$json['status'] = 'error';

			if ($json['message'] == '')
			{
				$json['message'] = 'Server error.';
			}
		}

		echo json_encode($json);
	}

	public function ajax_delete($attendance_id = 0)
	{
		$json['status'] = 'success';

		try
		{
			$this->db->trans_start();

			if ($attendance_id <= 0)
			{
				throw new Exception();
			}

			if ($this->session->userdata('user_id') != $this->_user->id)
			{
				throw new Exception('Server Error. Please log out first.');
			}

			$acl = $this->_acl;

			if (!isset($acl['attendance']) || $acl['attendance']->delete <= 0)
			{
				throw new Exception('You have no access to delete attendance. Please contact your administrator.');
			}

			$attendance = $this->core_model->get('attendance', $attendance_id);
			$updated = $_POST['updated'];
			$attendance_record = array();

			foreach ($attendance as $k => $v)
			{
				if ($k == 'updated' && $v != $updated)
				{
					throw new Exception('This data has been updated by another attendance. Please refresh the page.');
				}
				else
				{
					$attendance_record[$k] = $v;
				}
			}

			$this->_validate_delete($attendance_id);

			$this->core_model->delete('attendance', $attendance_id);
			$attendance_record['id'] = $attendance->id;
			$attendance_record['last_query'] = $this->db->last_query();

			$this->cms_function->system_log($attendance_record['id'], 'delete', $attendance_record, array(), 'attendance');

			if ($this->_has_image > 0)
			{
				$this->db->where('attendance_id', $attendance_id);
	            $arr_image = $this->core_model->get('image');

	            foreach ($arr_image as $image)
	            {
	                unlink("images/website/{$image->id}.{$image->ext}");

	                $this->core_model->delete('image', $image->id);
	            }
			}

			$this->db->trans_complete();
		}
		catch (Exception $e)
		{
			$json['message'] = $e->getMessage();
			$json['status'] = 'error';

			if ($json['message'] == '')
			{
				$json['message'] = 'Server error.';
			}
		}

		echo json_encode($json);
	}

	public function ajax_edit($attendance_id)
	{
		$json['status'] = 'success';

		try
		{
			$this->db->trans_start();

			if ($this->session->userdata('user_id') != $this->_user->id)
			{
				throw new Exception('Server Error. Please log out first.');
			}

			$acl = $this->_acl;

			if (!isset($acl['attendance']) || $acl['attendance']->edit <= 0)
			{
				throw new Exception('You have no access to edit attendance. Please contact your administrator.');
			}

			$old_attendance = $this->core_model->get('attendance', $attendance_id);

			$old_attendance_record = array();

			foreach ($old_attendance as $key => $value)
			{
				$old_attendance_record[$key] = $value;
			}

			$attendance_record = array();
			$image_id = 0;

			foreach ($_POST as $k => $v)
			{
				if ($k == 'updated')
				{
					if ($v != $old_attendance_record[$k])
					{
						throw new Exception('This data has been updated by another user. Please refresh the page.');
					}
				}
				elseif ($k == 'image_id')
                {
                    $image_id = $v;
                }
				else
				{
					$attendance_record[$k] = ($k == 'date') ? strtotime($v) : $v;
				}
			}

			$this->_validate_edit($attendance_id, $attendance_record);

			$this->core_model->update('attendance', $attendance_id, $attendance_record);
			$attendance_record['id'] = $attendance_id;
			$attendance_record['last_query'] = $this->db->last_query();

			$this->cms_function->system_log($attendance_record['id'], 'edit', $attendance_record, $old_attendance_record, 'attendance');

			$this->cms_function->update_foreign_field(array('product'), $attendance_record, 'attendance');

			if ($image_id > 0)
            {
                $this->db->where('attendance_id', $attendance_id);
                $arr_image = $this->core_model->get('image');

                foreach ($arr_image as $image)
                {
                    unlink("images/website/{$image->id}.{$image->ext}");

                    $this->core_model->delete('image', $image->id);
                }

                $this->core_model->update('image', $image_id, array('attendance_id' => $attendance_id));
            }

			$this->db->trans_complete();
		}
		catch (Exception $e)
		{
			$json['message'] = $e->getMessage();
			$json['status'] = 'error';

			if ($json['message'] == '')
			{
				$json['message'] = 'Server error.';
			}
		}

		echo json_encode($json);
	}

	public function ajax_get($attendance_id = 0)
	{
		$json['status'] = 'success';

		try
		{
			if ($attendance_id <= 0)
			{
				throw new Exception();
			}

			$attendance = $this->core_model->get('attendance', $attendance_id);

			$json['attendance'] = $attendance;
		}
		catch (Exception $e)
		{
			$json['message'] = $e->getMessage();
			$json['status'] = 'error';

			if ($json['message'] == '')
			{
				$json['message'] = 'Server error.';
			}
		}

		echo json_encode($json);
	}

	function ajax_upload($month = 0, $year = 0)
	{
		$json['status'] = 'success';

		try
		{
			if ($month == 0 && $year == 0)
			{
				$month = date('m', time());
				$year = date('Y', time());
			}

			$source_path = $_FILES['file']['tmp_name'];
			$ext = pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION);

			$target_path = 'files/' . $_FILES['file']['name'];
			move_uploaded_file($source_path, $target_path);
			chmod($target_path, 0777);

			$file = file_get_contents(base_url() . 'files/' . $_FILES['file']['name'], FILE_USE_INCLUDE_PATH);

			$date_from = strtotime($year . '-' . $month . '-01');
			$month_to = ($month == 12) ? '01' : $month + 1;
			$year_to = ($month == 12) ? $year + 1 : $year;
			$date_to = strtotime($year_to . '-' . $month_to . '-01');

			//delete attendance
			$this->db->where('date >=', $date_from);
			$this->db->where('date <', $date_to);
			$this->db->where('imported >', 0);
			$this->core_model->delete('attendance');

			$arr_file = explode("\n", $file);
			$arr_attendance_lookup = array();
			$arr_absence = array();
			$arr_old_attendance_lookup = array();
			$arr_batch_attendance_record = array();
			$count = 0;

			foreach ($arr_file as $files)
			{
				$attendances = preg_split('/\s+/', $files);

				foreach ($attendances as $k => $v)
				{
					if ($v == '')
					{
						continue;
					}

					if ($k > 3)
					{
						continue;
					}

					$arr_attendance_lookup[$count][] = $v;
				}

				$count += 1;
			}

			foreach ($arr_attendance_lookup as $attendance)
			{
				$date = strtotime($attendance[1]);

				if ($date < $date_from || $date > $date_to)
				{
					continue;
				}

				$arr_absence[$attendance[0]][$date][] = $attendance[2];
			}

			$this->db->where('date >=', $date_from);
			$this->db->where('date <', $date_to);
			$this->db->where('imported <=', 0);
			$arr_old_attendance = $this->core_model->get('attendance');

			foreach ($arr_old_attendance as $old_attendance)
			{
				$arr_old_attendance_lookup[$old_attendance->user_id][$old_attendance->date] = clone $old_attendance;
			}

			$this->db->select('id, absence_number');
			$this->db->where('absence_number >', 0);
			$this->db->where('active >', 0);
			$this->db->order_by('id');
			$arr_user = $this->core_model->get('user');

			foreach ($arr_user as $user)
			{
				if (!isset($arr_absence[$user->absence_number]))
				{
					continue;
				}

				foreach ($arr_absence[$user->absence_number] as $k => $absences)
				{
					if (isset($arr_old_attendance_lookup[$user->id][$k]))
					{
						continue;
					}

					$attendance_record = array();
					$attendance_record['user_id'] = $user->id;
					$attendance_record['date'] = $k;

					$attendance_record['in'] = '';
					$attendance_record['out'] = '';
					$attendance_record['status'] = '';
					$attendance_record['count_late'] = 0;

					foreach ($absences as $k => $absence)
					{
						if ($attendance_record['in'] != '')
						{
							continue;
						}

						$times = explode(':', $absence);
						$count_late = 0;

						$attendance_record['in'] = ((int)$times[0] < 13) ? $times[0] . ':' . $times[1] : '';
						$attendance_record['status'] = '';

						if ((int)$times[0] >= 10)
						{
							if ((int)$times[0] > 10 && (int)$times[1] <= 0)
							{
								$count_late = ((int)$times[0] - 10) * 2;
							}
							else
							{
								$count_late = ((int)$times[1] > 0) ? $count_late + 1 : 0;
								$count_late = ((int)$times[1] > 30) ? $count_late + 1 : $count_late;
								$count_late = ((int)$times[0] > 10) ? ((((int)$times[0] - 10)) * 2) + $count_late : $count_late;
							}

							$attendance_record['status'] = ($count_late <= 0) ? '' : 'Late';
							$attendance_record['count_late'] = ($attendance_record['in'] != '') ? $count_late : 0;
						}
					}

					foreach ($absences as $k => $absence)
					{
						$times = explode(':', $absence);

						$attendance_record['out'] = ((int)$times[0] >= 13) ? $times[0] . ':' . $times[1] : '';
					}

					$attendance_record['status'] = ($attendance_record['in'] == '' || $attendance_record['out'] == '') ? 'Alpa' : $attendance_record['status'];
					$attendance_record['imported'] = 1;

					$attendance_record = $this->cms_function->populate_foreign_field($attendance_record['user_id'], $attendance_record, 'user');

					$attendance_record['created'] = null;
					$attendance_record['author_id'] = $this->_user->id;
					$arr_batch_attendance_record[] = $attendance_record;
				}
			}

			if (count($arr_batch_attendance_record) > 0)
			{
				$this->db->insert_batch('attendance', $arr_batch_attendance_record);
			}

			$json['month'] = $month;
			$json['year'] = $year;
		}
		catch (Exception $e)
		{
			$json['message'] = $e->getMessage();
			$json['status'] = 'error';

			if ($json['message'] == '')
			{
				$json['message'] = 'Server error.';
			}
		}

		echo json_encode($json);
	}




	private function _get_user()
	{
		$this->db->where('id >', 2);
		$this->db->where('active >', 0);
		$this->db->order_by('name');
		return $this->core_model->get('user');
	}

	private function _validate_add($attendance_record)
	{
		$this->db->where('name', $attendance_record['name']);
		$count_user = $this->core_model->count('attendance');

		if ($count_user > 0)
		{
			throw new Exception('Name already exist.');
		}
	}

	private function _validate_delete($attendance_id)
	{
		$this->db->where('deletable <=', 0);
		$this->db->where('id', $attendance_id);
		$count_user = $this->core_model->count('attendance');

		if ($count_user > 0)
		{
			throw new Exception('Data cannot be deleted.');
		}
	}

	private function _validate_edit($attendance_id, $attendance_record)
	{
		$this->db->where('editable <=', 0);
		$this->db->where('id', $attendance_id);
		$count_user = $this->core_model->count('attendance');

		if ($count_user > 0)
		{
			throw new Exception('Data cannot be updated.');
		}

		$this->db->where('id !=', $attendance_id);
		$this->db->where('name', $attendance_record['name']);
		$count_user = $this->core_model->count('attendance');

		if ($count_user > 0)
		{
			throw new Exception('Name is already exist.');
		}
	}
}