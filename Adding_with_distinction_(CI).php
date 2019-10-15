class AModel extends CI_Model
{
    public function  get()
    {
        $this->db->select('*');
        $this->db->distinct('id, user_id, created_at, created_by,  updated_at, updated_by');
        if (!($q = $this->db->get('table_name'))) {
            throw new Exception("Could not update user : " . print_r($this->db->error(), true));
        }
        $userNames = [];
        foreach ($q->result()[0] as $key => $value) {
            if ($key != 'user_id' && $key != 'created_at' && $key !=  'created_by' && $key != 'updated_at' && $key !=  'updated_by') {
                $userNames[$key] = $key;
            }
        }
        return $userNames;
    }
}
