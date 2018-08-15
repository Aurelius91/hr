<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Patch_user extends CI_Controller
{
	private $_setting;

	public function __construct()
	{
		parent:: __construct();

		$this->_setting = $this->setting_model->load();
	}




	public function generate()
	{
		$json['status'] = 'success';

		try
		{
			$this->db->trans_start();

			$this->db->query('USE admin_1_labelideas');

			$this->db->where('id >', 1);
			$arr_user = $this->core_model->get('user');

			$this->db->query('USE hr_1_label');

			$user_record = array();

			foreach ($arr_user as $key => $user)
			{
				if ($user->id == '28')
				{
					continue;
				}

				$user_record = array();
				$user_record['absence_number'] = $user->absence_id;
				$user_record['department_id'] = $user->department_id;
				$user_record['type'] = 'Staff';
				$user_record['name'] = $user->name;
				$user_record['position'] = $user->position;
				$user_record['nip'] = $user->nip;
				$user_record['address'] = $user->address;
				$user_record['email'] = $user->email;
				$user_record['ktp'] = $user->ktp;
				$user_record['npwp'] = $user->npwp;
				$user_record['religion'] = $user->religion;
				$user_record['status'] = $user->status;
				$user_record['active'] = $user->active;
				$user_record['username'] = $user->username;
				$user_record['password'] = md5($user->password);

				$user_record = $this->cms_function->populate_foreign_field($user_record['department_id'], $user_record, 'department');
				$user_id = $this->core_model->insert('user', $user_record);
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

	public function password()
	{
		$json['status'] = 'success';

		try
		{
			$this->db->trans_start();

			$this->db->where('id >', 2);
			$arr_user = $this->core_model->get('user');

			foreach ($arr_user as $user)
			{
				$user_record = array();
				$user_record['password'] = md5($user->username);
				$this->core_model->update('user', $user->id, $user_record);
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
}