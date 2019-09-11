<?php
defined("BASEPATH") OR exit("No direct script access allowed");

/**
* 
*/
class User_extend_model extends MY_Model
{
	function __construct()
	{
		parent::__construct();
	}

	public function add_rules()
	{
		$this->form_validation->set_rules("user_id","User ID","is_natural|is_unique[users.id]");
		$this->form_validation->set_rules("first_name","First Name","required|max_length[500]|trim");
		$this->form_validation->set_rules("last_name","Last Name","required|max_length[500]|trim");
		$this->form_validation->set_rules("email","Email","required|valid_email|max_length[128]|trim|is_unique[users.email]");
		$this->form_validation->set_rules("expireduser","Expired User","required|is_natural_no_zero");
		$this->form_validation->set_rules("quota","Quota","required|numeric");
		$this->form_validation->set_rules("group_campus","Reporting Group","required");
	}

	public function edit_restriction($id, $user = NULL)
	{
		$user = isset($user) ? $user : $this->ion_auth->user($id);
		$this->load->model("Group_model");
		if ($user->num_rows() < 1) {
			$this->session->set_flashdata("message","ID User Not Found");
			redirect("en_us/user","refresh");
		}
		if ( ! $this->ion_auth->in_group("cho admin")) {
			$universitas_user = $this->Group_model->get_user_campus()->row();
			if ($this->ion_auth->in_group("cho admim",$id)) {
				$this->session->set_flashdata("message","You are not allowed to edit this user");
				redirect("en_us/user","refresh");
			}
			if (!$this->ion_auth->in_group($universitas_user->id,$id)) {
				$this->session->set_flashdata("message","You are not allowed to edit this user");
				redirect("en_us/user","refresh");
			}
		}
	}

	public function edit_rules()
	{
		$this->form_validation->set_rules("first_name","First Name","required|max_length[500]|trim");
		$this->form_validation->set_rules("last_name","Last Name","required|max_length[500]|trim");
		$this->form_validation->set_rules("email","Email","required|valid_email|max_length[128]|trim");

		if ($this->input->post("password"))
		{
			$this->form_validation->set_rules("password","Password","required|matches[password_chk]|min_length[" . $this->config->item("min_password_length", "ion_auth") . "]");
			$this->form_validation->set_rules("password_chk","Password Confirm","required");
		}
	}

	public function create_user_trash($id_user = NULL)
	{
		$id_user = isset($id_user) ? $id_user : $this->session->userdata("user_id");
		$this->load->model("group/Group_folder_model");
		$user_trash = $this->Group_folder_model->get_data_trash($id_user);
		$count_user_trash = $user_trash->num_rows();
		if ($count_user_trash < 1) {
			$make_trash = $this->Group_folder_model->add_group_folder("Trash",$id_user);
			if ($make_trash) {
				$this->create_group_home_folder($id_user);
			} else {
				$this->session->set_flashdata("message","Failed to make Trash");
				redirect("en_us/user","refresh");
			}
		} else {
			$this->session->set_flashdata("message","You already have a trash folder");
			redirect("en_us/user","refresh");
		}
	}

	public function create_group_home_folder($id_user = NULL)
	{
		$id_user = isset($id_user) ? $id_user : $this->session->userdata("user_id");
		$this->load->model("group/Group_folder_model");
		$group_folders = $this->Group_folder_model->get_group_folders(0,$id_user);
		$count_group_folders = $group_folders->num_rows();
		if ($count_group_folders < 1) {
			$make_my_doc = $this->Group_folder_model->add_group_folder("My Documents",$id_user);
			if ($make_my_doc) {
				$this->create_home_folder($make_my_doc, $id_user);
			} else {
				$this->session->set_flashdata("message","Failed to make Group Folder");
				redirect("en_us/user","refresh");
			}
		} else {
			$this->session->set_flashdata("message","You already have a Group Folder");
			redirect("en_us/user","refresh");
		}
	}

	public function create_home_folder($id_document, $id_user = NULL)
	{
		$id_user = isset($id_user) ? $id_user : $this->session->userdata("user_id");
		$this->load->model("group/Group_folder_model");
		$this->load->model("folder/Folder_model");
		if (isset($id_document) && !empty($id_document)) {
			$folders = $this->Group_folder_model->get_group_folders($id_document,$id_user);
			$count_folders = $folders->num_rows();
			if ($count_folders < 1) {
				$make_my_folder = $this->Folder_model->add_folder("My Folders",$id_document,$id_user);
				if ($make_my_folder) {
					$this->set_home_folder($make_my_folder,$id_user);
				} else {
					$this->session->set_flashdata("message","Failed to make My Folder");
					redirect("en_us/user","refresh");
				}
			} else {
				$this->session->set_flashdata("message","You already have a My Documents");
				redirect("en_us/user","refresh");
			}
		} else {
			$this->session->set_flashdata("message","ID Group Folder cannot empty");
			redirect("en_us/user","refresh");
		}
	}

	public function set_home_folder($id_folder, $id_user = NULL)
	{
		$this->load->model("Group_model");
		$id_user = isset($id_user) ? $id_user : $this->session->userdata("user_id");
		if (isset($id_folder) && !empty($id_folder)) {
			$user_data = array();
			$user_data["home_folder"] = $id_folder;
			$set_home_folder = $this->ion_auth->update($id_user, $user_data);
			if ($set_home_folder) {
				$this->load->model("quota/Quota_model");
				$this->Quota_model->add_user();
			} else {
				$this->session->set_flashdata("message","Failed to set home folder");
				redirect("en_us/user","refresh");
			}
		} else {
			$this->session->set_flashdata("message","ID Folder cannot empty");
			redirect("en_us/user","refresh");
		}
	}
}