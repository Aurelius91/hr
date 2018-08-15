<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Report extends CI_Controller
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

		// load cms_excel
		$this->load->library('cms_excel');
	}




	public function export_employee()
	{
		$acl = $this->_acl;

		if (!isset($acl['user']) || $acl['user']->list <= 0)
		{
			redirect(base_url());
		}

		$date_display = date('Y-m-d', time());

		$arr_record = $this->_employee();

		$title = 'Employee Lists';
		$objPHPExcel = $this->cms_excel->create_excel($title);
		$objPHPExcel->getActiveSheet()->setCellValue('A1', 'Employee Lists');
		$objPHPExcel->getActiveSheet()->setCellValue('A2', 'Label Ideas & Co.: ');
		$this->cms_excel->setbold($objPHPExcel, array('A1', 'A2'));
		$this->cms_excel->setmerge($objPHPExcel, array('A1:J1', 'A2:J2'));
		$this->cms_excel->setautosize($objPHPExcel, array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'));

		$row = 4;

		$objPHPExcel->getActiveSheet()->setCellValue("A{$row}", 'NIP');
		$objPHPExcel->getActiveSheet()->setCellValue("B{$row}", 'Name');
		$objPHPExcel->getActiveSheet()->setCellValue("C{$row}", 'Position');
		$objPHPExcel->getActiveSheet()->setCellValue("D{$row}", 'Religion');
		$objPHPExcel->getActiveSheet()->setCellValue("E{$row}", 'KTP');
		$objPHPExcel->getActiveSheet()->setCellValue("F{$row}", 'NPWP');
		$objPHPExcel->getActiveSheet()->setCellValue("G{$row}", 'Birthday');
		$objPHPExcel->getActiveSheet()->setCellValue("H{$row}", 'Address');
		$objPHPExcel->getActiveSheet()->setCellValue("I{$row}", 'Date In');
		$objPHPExcel->getActiveSheet()->setCellValue("J{$row}", 'Status');
		$this->cms_excel->setborder($objPHPExcel, "A{$row}", "J{$row}", '#000');
		$this->cms_excel->setbold($objPHPExcel, array("A{$row}", "B{$row}", "C{$row}", "D{$row}", "E{$row}", "F{$row}", "G{$row}", "H{$row}", "I{$row}", "J{$row}"));

		foreach ($arr_record['arr_user'] as $user)
		{
			$row += 1;

			$objPHPExcel->getActiveSheet()->setCellValue("A{$row}", $user->nip);
			$objPHPExcel->getActiveSheet()->setCellValue("B{$row}", $user->name);
			$objPHPExcel->getActiveSheet()->setCellValue("C{$row}", $user->position);
			$objPHPExcel->getActiveSheet()->setCellValue("D{$row}", $user->religion);
			$objPHPExcel->getActiveSheet()->setCellValueExplicit("E{$row}", $user->ktp);
			$objPHPExcel->getActiveSheet()->setCellValueExplicit("F{$row}", $user->npwp);
			$objPHPExcel->getActiveSheet()->setCellValue("G{$row}", $user->birthday_display);
			$objPHPExcel->getActiveSheet()->setCellValue("H{$row}", $user->address);
			$objPHPExcel->getActiveSheet()->setCellValue("I{$row}", $user->date_in_display);
			$objPHPExcel->getActiveSheet()->setCellValue("J{$row}", $user->status);
			$this->cms_excel->setborder($objPHPExcel, "A{$row}", "J{$row}", '#000');
		}

		$this->cms_excel->download_excel($objPHPExcel, $title);
	}




	private function _employee()
	{
		$this->db->where('id >', 2);
		$this->db->where('active >', 0);
		$arr_user = $this->core_model->get('user');

		foreach ($arr_user as $user)
		{
			$user->birthday_display = ($user->birthday > 0) ? date('d F Y', $user->birthday) : '';
			$user->date_in_display = ($user->date_in > 0) ? date('d F Y', $user->date_in) : '';
			$user->address = strip_tags($user->address);
		}

		$arr_record = array();
		$arr_record['arr_user'] = $arr_user;

		return $arr_record;
	}

	private function _get_location()
	{
		if ($this->_user->location_id > 0)
		{
			$this->db->where('id', $this->_user->location_id);
		}

		$this->db->order_by('name');
		return $this->core_model->get('location');
	}
}