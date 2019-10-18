<?php
class give extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->dbforge();
    }
    public function up()
    {
        $now = (new DateTime())->format($this->config->item('datetime_format', 'country'));
        $allRoles = [];
        $this->db->trans_begin();
        $this->db->set('acs_name', 'DB_Admin');
        $this->db->set('created_at', $now);
        $this->db->set('updated_at', $now);
        if (!$this->db->insert('table_name')) {
            $error = $this->db->error();
            $this->db->trans_rollback();
            throw new Exception('Could not insert table_name: ' . print_r($error, true));
        }
        $this->newRoleId = $this->db->insert_id();
        if ($q = $this->db->get('company_role')) {
            foreach ($q->result() as $item) {
                $allRoles[$item->role_name] = $item->id;
            }
        } else {
            $error = $this->db->error();
            $this->db->trans_rollback();
            throw new Exception('Could not get admin role IDs: ' . print_r($error, true));
        }
        foreach ($allRoles as $key => $value) {
            switch ($key) {
                case "Admin":
                case "User":
                case "Client":
                    $this->db->select('id');
                    $this->db->where('role_id', $value);
                    $this->db->where('facl_id', $this->companyName);
                    if (!($q = $this->db->get('org_role'))) {
                        throw new Exception('Could not get db_admin role ID: ' . print_r($this->db->error(), true));
                    }
                    $data = array(
                        array(
                            "role_org_id" => $q->result()[0]->id,
                            "acs_id" => $this->newRoleId,
                            "permission" => "C-R-U-D",
                            "created_at" => $now,
                            "updated_at" => $now,
                            'created_by' => 1,
                            'updated_by' => 1,
                        )
                    );
                    if ($this->db->insert_batch('org_role_acs', $data)) {
                        break;
                    } else {
                        $error = $this->db->error();
                        $this->db->trans_rollback();
                        throw new Exception('Could not add access controls for administrator role: ' . print_r($error, true));
                    }
            }
        }
        $this->db->trans_commit();
        return true;
    }
   
    public function down()
    {
        $this->db->trans_begin();
        $this->db->select('id');
        $this->db->where('acs_name', 'DB_Admin');
        if (!($q = $this->db->get('table_name'))) {
            throw new Exception('Could not get access control Id : ' . print_r($this->db->error(), true));
        }
        $accessControlId =  $q->result()[0]->id;
        $allRoles = [];
        if ($q = $this->db->get('company_role')) {
            foreach ($q->result() as $item) {
                $allRoles[$item->role_name] = $item->id;
            }
        } else {
            $error = $this->db->error();
            $this->db->trans_rollback();
            throw new Exception('Could not get admin role IDs: ' . print_r($error, true));
        }
        foreach ($allRoles as $key => $value) {
            switch ($key) {
                case "Administrator":
                case "User":
                case "Client":
                    $this->db->select('id');
                    $this->db->where('role_id', $value);
                    $this->db->where('facl_id', $this->companyName);
                    if (!($q = $this->db->get('org_role'))) {
                        throw new Exception('Could not get role facility ID: ' . print_r($this->db->error(), true));
                    }
                    $this->db->where('role_org_id', $q->result()[0]->id);
                    $this->db->where('acs_id', $accessControlId);
                    if (!$this->db->delete('org_role_acs')) {
                        $error = $this->db->error();
                        $this->db->trans_rollback();
                        throw new Exception('Could not delete permissions of DB_Admin: ' . print_r($error, true));
                    }
            }
        }
        $this->db->where('acs_name', 'DB_Admin');
        if (!$this->db->delete('table_name')) {
            $error = $this->db->error();
            $this->db->trans_rollback();
            throw new Exception('Could not delete DB_Admin access control: ' . print_r($error, true));
        }
        $this->db->trans_commit();
        return true;
    } 
   
}
 
