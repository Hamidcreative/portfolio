<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Inventory extends MY_Controller {
	public $inventoryfields;
    public function __construct(){
        parent::__construct();
    	$this->inventoryfields = [
		        ['field' => 'description', 'label' => 'Description', 'rules' => 'required'],
		        ['field' => 'warehouse_id', 'label' => 'Warehouse', 'rules' => 'required'],
		        ['field' => 'amount', 'label' => 'Amount', 'rules' => 'required'],
		        ['field' => 'inventory_type_id', 'label' => 'Inventory Type', 'rules' => 'required'],
		        ['field' => 'min_level', 'label' => 'Minimum Level', 'rules' => 'required'],
			];
		$this->inventorytransferfields = [
		        ['field' => 'item_id', 'label' => 'Item Id', 'rules' => 'required'],
		        ['field' => 'quantity', 'label' => 'Amount', 'rules' => 'required'],
			];
    }
	public function index()
	{
		$where_in = '';
        if(isEndUser($this->session->userdata('user')->id)){
            $warehouseIds = getUserWareHouseIds($this->session->userdata('user')->id);
            $where_in = ['col'=>'id', 'val'=>$warehouseIds];
        }
        $data = [
        	'warehouses'=>$this->Common_model->select_fields_where('warehouse','*', '', FALSE, '', '', '', '', '', false, $where_in)
        ];
		$this->show('inventory/listing', $data);
	}
	public function listing($warehouse_id=''){
		$select_data = ['wi.id as ID ,i.item_id as item_id, i.description as description, it.name as inventory_type,i.serial_number as serial_number, wi.status as status', false];
		$joins = [
			['table'=>'inventory_type it', 'condition'=>'i.inventory_type_id = it.id', 'type'=>'left'],
			['table'=>'warehouse_inventory wi', 'condition'=>'wi.inventory_id = i.id', 'type'=>'inner'],
		];
		$warehouseIds = '';
		$warehouseId = '';
		if(isEndUser($this->session->userdata('user')->id)){
			$warehouseIds = getUserWareHouseIds($this->session->userdata('user')->id);
			$warehouseId = 'warehouse_id';
		}
        $addColumns = array(
            'actionButtons' => array('<a href="'.base_url().'inventory/$1/'.$warehouse_id.'"><i class="material-icons">edit</i></a><a href="#" class="confirm-modal-trigger" data-id="$1"><i class="material-icons">delete</i></a>','ID')
        );
        $where = [];
        $group_by = '';
        // filter code 
        if($warehouse_id == ''){
        	$warehouse_id = $this->input->post('warehouse');
	       	if(!empty($this->input->post('serial_no')))
	       		$where['i.serial_number'] = $this->input->post('serial_no');
	       
	       	if($this->input->post('min_level') != 'false'){
	       		$group_by = '';
	       		$joins[1]['condition'] = 'wi.inventory_id = i.id and wi.min_level >= wi.quantity';
        		$select_data[0] .= ', wi.quantity as quantity, wi.min_level as min_level';
	       	} else {
	       		$group_by = 'i.item_id';
        		$select_data[0] .= ', SUM(wi.quantity) as quantity, MIN(wi.min_level) as min_level';
	       	}
        } else {
        	$select_data[0] .= ', wi.quantity as quantity, wi.min_level as min_level';
        }
       	if($warehouse_id != '')
       		$where['wi.warehouse_id'] = $warehouse_id;
       	if(empty($where)) $where = '';
       	
        $list = $this->Common_model->select_fields_joined_DT($select_data,'inventory i',$joins,$where,$warehouseId, $warehouseIds, $group_by, $addColumns);

        print $list;
	}
	public function spare_parts(){ // spare parts by warehouse
		$where_in = '';
		if(isEndUser($this->session->userdata('user')->id)){
			$warehouseIds = getUserWareHouseIds($this->session->userdata('user')->id);
			$where_in = ['col'=>'id', 'val'=>$warehouseIds];
		}
		$data = [
			'warehouses'=>$this->Common_model->select_fields_where('warehouse','*', '', FALSE, '', '', '', '', '', false, $where_in)
		];
		$this->show('inventory/spare_parts_by_warehouse', $data);
	}
	public function spare_listing($warehouse_id=''){  // spare parts by warehouse
		$select_data = ['wi.id as ID ,i.item_id as item_id, i.description as description, it.name as inventory_type,i.serial_number as serial_number, w.name as warehouse', false];
		$joins = [
			['table'=>'inventory_type it', 'condition'=>'i.inventory_type_id = it.id', 'type'=>'left'],
			['table'=>'warehouse_inventory wi', 'condition'=>'wi.inventory_id = i.id', 'type'=>'inner'],
			['table'=>'warehouse w', 'condition'=>'wi.warehouse_id = w.id', 'type'=>'inner'],
		];
		$warehouseIds = '';
		$warehouseId = '';
		if(isEndUser($this->session->userdata('user')->id)){
			$warehouseIds = getUserWareHouseIds($this->session->userdata('user')->id);
			$warehouseId = 'warehouse_id';
		}		 
		$where = [];
		$group_by = '';
		// filter code
		if($warehouse_id == ''){
			$warehouse_id = $this->input->post('warehouse');
			if(!empty($this->input->post('serial_no')))
				$where['i.serial_number'] = $this->input->post('serial_no');

			if($this->input->post('min_level') != 'false'){
				$group_by = '';
				$joins[1]['condition'] = 'wi.inventory_id = i.id and wi.min_level >= wi.quantity';
				$select_data[0] .= ', wi.quantity as quantity, wi.min_level as min_level';
			} else {
				$select_data[0] .= ', wi.quantity as quantity, wi.min_level as min_level';
			}
		} else {
			$select_data[0] .= ', wi.quantity as quantity, wi.min_level as min_level';
		}
		if($warehouse_id != '')
			$where['wi.warehouse_id'] = $warehouse_id;
		if(empty($where)) $where = '';

		$list = $this->Common_model->select_fields_joined_DT($select_data,'inventory i',$joins,$where,$warehouseId, $warehouseIds, '', '');

		print $list;
	}
	public function minlevellisting(){
		$select_data = ['WHI.id as ID ,i.item_id, i.serial_number as serial_number,i.description,WH.name as WHname, WHI.quantity, WHI.min_level', false];
		$where = '';
		$joins = array(
			array(
				'table'     => 'warehouse_inventory WHI',
				'condition' => 'WHI.inventory_id = i.id',
				'type'      => 'Right'
			),array(
				'table'     => 'warehouse WH',
				'condition' => 'WH.id = WHI.warehouse_id',
				'type'      => 'Right'
			)
		);
		$where = 'WHI.min_level >= WHI.quantity';
		$returnedData = $this->Common_model->select_fields_joined_DT($select_data,'inventory i',$joins,$where,'','','','');
		print_r($returnedData);
		return NULL;
	}
	public function edit($warehouseInventoryId, $warehouseId=''){
		if(!empty($warehouseId))
			$url = 'warehouse/view/'.$warehouseId;
		else
			$url = 'inventory';
		$inventoryId = $this->input->post('inventory_id');
        if(isEndUser($this->session->userdata('user')->id)) 
            return redirect($url);
        if(!isAdministrator($this->session->userdata('user')->id)){
			$warehouseIds = getUserWareHouseIds($this->session->userdata('user')->id);
			$inventory = $this->Common_model->select_fields_where('warehouse_inventory','warehouse_id',['id'=>$warehouseInventoryId], true);
			// stop user editing spare part of other ware house
			if(!in_array($inventory->warehouse_id, $warehouseIds))
				return redirect($url);
		}
		if($this->input->method() == 'post'){
			$existingInventory = $this->Common_model->select_fields_where('inventory','item_id',['id'=>$inventoryId], true);
			if($this->input->post('item_id') != $existingInventory->item_id)
		       $is_unique_item_id =  '|is_unique[inventory.item_id]';
		    else 
		       $is_unique_item_id =  '';
			array_push($this->inventoryfields, ['field' => 'item_id', 'label' => 'Item No.', 'rules' => 'required'.$is_unique_item_id]);
			$this->form_validation->set_rules($this->inventoryfields);
			if ($this->form_validation->run() == FALSE) {
				$this->session->set_flashdata('alert', ['type'=>'error', 'message'=>'Invalid data']);
			}
			else {
				$data = [
					'item_id' => $this->input->post('item_id'),
					'description' => $this->input->post('description'),
					'serial_number' => $this->input->post('serial_number'),
					'inventory_type_id' => $this->input->post('inventory_type_id'),
					'updated_at' => date('Y-m-d h:i:s'),
				];
				$update = $this->Common_model->update('inventory',['id'=>$inventoryId], $data);
				if($update){
					$whID = $this->input->post('warehouse_id');
					$data = [
						'warehouse_id' => $whID,
						'min_level' => $this->input->post('min_level'),
						'quantity' => $this->input->post('amount'),
						'updated_at' => date('Y-m-d h:i:s'),
					];
					// insert into main inventory 
					$this->Common_model->update('warehouse_inventory',['id'=>$warehouseInventoryId], $data);
					$this->session->set_flashdata('alert', ['type'=>'success', 'message'=>'Inventory info updated successfully']);
					$activity = array('warehouse_id' =>$whID,'model_id' => $whID,'method' => 'Updated', 'model_name' => 'Spare','name'=> $this->input->post('item_id'),'detail'=> 'Updated Spare Part','rout'=>'inventory/'.$warehouseInventoryId);
					logs($activity);
					redirect($url);
				} else {
					$this->session->set_flashdata('alert', ['type'=>'error', 'message'=>'Error updating']);
					if(!empty($warehouseId))
						redirect('inventory/'.$warehouseInventoryId.'/'.$warehouseId);
					else
						redirect('inventory/'.$warehouseInventoryId);
				}
			}
		} else if($this->input->method() == 'delete') {
			$joins = [
				['table'=>'warehouse_inventory wi', 'condition'=>'wi.inventory_id = i.id', 'type'=>'inner']
			];
			$inventory = $this->Common_model->select_fields_where_like_join('inventory i', 'i.item_id, wi.warehouse_id', $joins, ['wi.id'=>$warehouseInventoryId], true );
			$deleted = $this->Common_model->delete('warehouse_inventory',['id'=>$warehouseInventoryId]);
			if($deleted){
				echo json_encode(['type'=>'success','message'=>'One item has been deleted successfully']);
				$activity = array('warehouse_id' =>$inventory->warehouse_id,'model_id' => $inventory->warehouse_id,'method' => 'Deleted', 'model_name' => 'Spare','name'=> $inventory->item_id,'detail'=> 'Spare Part Deleted','rout'=>'');
				logs($activity);
			}
			else{
				echo json_encode(['type'=>'error','message'=>'Item not deleted']);
			}
			exit;
		} else if($this->input->method() == 'patch') {
			$data = $this->input->input_stream();
			$joins = [
				['table'=>'warehouse_inventory wi', 'condition'=>'wi.inventory_id = i.id', 'type'=>'inner']
			];
			$inventory = $this->Common_model->select_fields_where_like_join('inventory i', 'i.item_id, wi.warehouse_id', $joins, ['wi.id'=>$warehouseInventoryId], true );
			$updated = $this->Common_model->update('warehouse_inventory', ['id'=>$warehouseInventoryId], $data);
			if($updated){
				echo json_encode(['type'=>'success','message'=>'Item status updated successfully']);
				$activity = array('warehouse_id' =>$inventory->warehouse_id,'model_id' => $inventory->warehouse_id,'method' => 'Status Updated', 'model_name' => 'Spare','name'=> $inventory->item_id,'detail'=> 'Status Updated','rout'=>'inventory/'.$warehouseInventoryId);
				logs($activity);
			}
			else 
				echo json_encode(['type'=>'error','message'=>'Error updating item status']);
			exit;
		}
		$where_in = '';
		if(!isAdministrator($this->session->userdata('user')->id)){
			$where_in = ['col'=>'id', 'val'=>getUserWareHouseIds($this->session->userdata('user')->id)];
		}
		$warehouses = $this->Common_model->select_fields_where('warehouse','*',['status'=>1], FALSE, '', '', '','','',false, $where_in);
		$joins = [
			['table'=>'warehouse_inventory wi', 'condition'=>'wi.inventory_id = i.id', 'type'=>'inner']
		];
		$inventories = $this->Common_model->select_fields_where_like_join('inventory i', 'i.item_id, i.description, i.serial_number, i.inventory_type_id, wi.*', $joins, ['wi.id'=>$warehouseInventoryId], true );
		$data = [
			'warehouses' => $warehouses,
			'inventory_types' => $this->Common_model->select_fields_where('inventory_type', '*', ['status'=>1]),
			'inventory' => $inventories,
			'warehouseId' => $warehouseId
		];
		$this->show('inventory/edit', $data);
	}
	public function add(){
        if(isEndUser($this->session->userdata('user')->id)) 
            return redirect('inventory');
		if($this->input->method() == 'post'){
			$this->form_validation->set_rules($this->inventoryfields);
			if ($this->form_validation->run() == FALSE) {
				$this->session->set_flashdata('alert', ['type'=>'error', 'message'=>'Invalid Input Data']);
			}
			else {
				// check item exist already then get its id
				$itemId = $this->input->post('item_id');
				$inventory = $this->Common_model->select_fields_where('inventory','id',['item_id'=>$itemId], true);
				if($inventory)
					$inventoryId = $inventory->id;
				else { // else add new inventory
					$data = [
						'item_id' => $itemId,
						'description' => $this->input->post('description'),
						'serial_number' => $this->input->post('serial_number'),
						'inventory_type_id' => $this->input->post('inventory_type_id'),
						'updated_at' => date('Y-m-d h:i:s'),
						'created_at' => date('Y-m-d h:i:s'),
					];
					// insert into main inventory table
					$inventoryId = $this->Common_model->insert_record('inventory', $data);
				}
				if($inventoryId){
					// check if exist alrady then add amount
					$warehouseId = $this->input->post('warehouse_id');
					$warehouseInventory = $this->Common_model->select_fields_where('warehouse_inventory','id, quantity',
						['inventory_id'=>$inventoryId, 'warehouse_id'=>$warehouseId], true);
					if($warehouseInventory){
						// update item amount
						$data = [
							'min_level' => $this->input->post('min_level'),
							'quantity' => $warehouseInventory->quantity + $this->input->post('amount'),
							'updated_at' => date('Y-m-d h:i:s'),
						];
						$this->Common_model->update('warehouse_inventory',['id'=>$warehouseInventory->id] ,$data);
						$this->session->set_flashdata('alert', ['type'=>'success', 'message'=>'Inventory item quantity added successfully']);
					}
					else {
						// insert into warehouse item
						$data = [
							'inventory_id' => $inventoryId,
							'warehouse_id' => $warehouseId,
							'min_level' => $this->input->post('min_level'),
							'quantity' => $this->input->post('amount'),
							'updated_at' => date('Y-m-d h:i:s'),
							'created_at' => date('Y-m-d h:i:s'),
						];
						// insert into main inventory table
						$this->Common_model->insert_record('warehouse_inventory', $data);
						$last_insertedid = $this->db->insert_id();
						$this->session->set_flashdata('alert', ['type'=>'success', 'message'=>'Inventory item added successfully']);
						$activity = array('warehouse_id' =>$warehouseId,'model_id' => $warehouseId,'method' => 'Added', 'model_name' => 'Spare','name'=> $itemId,'detail'=> 'Spare Added','rout'=>'inventory/'.$last_insertedid);
						logs($activity);
					}
					redirect('inventory');
				} else {
					$this->session->set_flashdata('alert', ['type'=>'error', 'message'=>'Error adding']);
					redirect('inventory/add');
				}
			}
		}
		$where_in = '';
		if(!isAdministrator($this->session->userdata('user')->id))
			$where_in = ['col'=>'id', 'val'=>getUserWareHouseIds($this->session->userdata('user')->id)];	 
		if(empty($where_in['val']) && !isAdministrator($this->session->userdata('user')->id)){
			echo json_encode(['type'=>'error','message'=>'Contact admin']);
			return redirect('users/'.$this->session->userdata('user')->id);// we will redirect it to message page 
		}
		$warehouses = $this->Common_model->select_fields_where('warehouse','*',['status'=>1], FALSE, '', '', '','','',false, $where_in);
		$data = [
			'warehouses' => $warehouses,
			'inventory_types' => $this->Common_model->select_fields_where('inventory_type', '*', ['status'=>1]),
		];
		$this->show('inventory/add', $data);
	}
	public function send_to_warehouse(){
		if($this->input->method() == 'post'){
			array_push($this->inventorytransferfields, ['field' => 'checkout_by', 'label' => 'Checkout By Person', 'rules' => 'required'], 
		        ['field' => 'from_warehouse_id', 'label' => 'From Warehouse', 'rules' => 'required'],
		        ['field' => 'to_warehouse_id', 'label' => 'To Warehouse', 'rules' => 'required']);
			$this->form_validation->set_rules($this->inventorytransferfields);
			if ($this->form_validation->run() == FALSE) {
				$this->session->set_flashdata('alert', ['type'=>'error', 'message'=>'Invalid Input Data']);
				redirect('inventory/send_to_warehouse');
			}
			else {
				$itemId = $this->input->post('item_id');
				$fromWarehouseId = $this->input->post('from_warehouse_id');
				$quantity = $this->input->post('quantity');
				// check item code exist and get existing amount for that item against that warehouse
				$existingItem = $this->Common_model->select_fields_where_like_join('inventory i', 'i.id, wi.quantity, wi.id as warehouseInventoryId',
				[
					['table'=>'warehouse_inventory wi', 'condition'=>'wi.inventory_id = i.id', 'type'=>'inner']
				],
				[
					'i.item_id' => $itemId,
					'wi.warehouse_id' => $fromWarehouseId,
					'wi.status' => 1,
				], true);
				if(!$existingItem) {
					$this->session->set_flashdata('alert', ['type'=>'error', 'message'=>'Item not exist in selected warehouse']);
					redirect('inventory/send_to_warehouse');
				}
				if($existingItem->quantity < $quantity) {
					$this->session->set_flashdata('alert', ['type'=>'error', 'message'=>'Not enough amount of items exist in selected warehouse']);
					redirect('inventory/send_to_warehouse');
				}
				$this->Common_model->update('warehouse_inventory',[
					'warehouse_id' => $fromWarehouseId,
					'status' => 1,
					'inventory_id' => $existingItem->id
				],[
					'quantity' => $existingItem->quantity - $quantity
				]);
				
				$data = [
					'warehouse_inventory_id' => $existingItem->warehouseInventoryId,
					'to_user_id' => $this->input->post('checkout_by'),
					'quantity' => $this->input->post('quantity'),
					'from_warehouse_id' => $fromWarehouseId,
					'to_warehouse_id' => $this->input->post('to_warehouse_id'),
					'from_user_id' => $this->session->userdata('user')->id,
					'type' => 1,
					'created_at' => date('Y-m-d h:i:s'),
				];
				$insertedId = $this->Common_model->insert_record('inventory_transfer', $data);
				if($insertedId){
					$this->session->set_flashdata('alert', ['type'=>'success', 'message'=>'Spare part send to warehouse successfully']);
					$fromWH = $this->Common_model->select_fields_where('warehouse','name', ['id'=>$fromWarehouseId], true);
					$toWH   = $this->Common_model->select_fields_where('warehouse','name', ['id'=>$this->input->post('to_warehouse_id')], true);
					$activity = array('warehouse_id' =>$fromWarehouseId,'model_id' => $fromWarehouseId,'method' => ''.$quantity.' Spare part sent from Warehouse'.$fromWH->name.' to '.$toWH->name.'', 'model_name' => 'Spare','name'=> $itemId,'detail'=> 'Spare part sent to warehouse','rout'=>'inventory/'.$existingItem->warehouseInventoryId);
					logs($activity);
					redirect('inventory');
				} else {
					$this->session->set_flashdata('alert', ['type'=>'error', 'message'=>'Error adding']);
					redirect('inventory/send_to_warehouse');
				}
			}
		}
		else {
			$where_in = '';
			if(!isAdministrator($this->session->userdata('user')->id)){
				$where_in = ['col'=>'id', 'val'=>getUserWareHouseIds($this->session->userdata('user')->id)];
			}
			$warehouses = $this->Common_model->select_fields_where('warehouse','*',['status'=>1], FALSE, '', '', '','','',false, $where_in);
			$data = [
				'users' => $this->Common_model->select_fields_where('user', '*', ['status'=>1]),
				'warehouses' => $warehouses,
				'inventory_types' => $this->Common_model->select_fields_where('inventory_type', '*', ['status'=>1])
			];
			$this->show('inventory/send_to_warehouse', $data);
		}
	}
	public function recieve_from_warehouse(){
		if($this->input->method() == 'post'){
			array_push($this->inventorytransferfields, ['field' => 'from_user_id', 'label' => 'From Person', 'rules' => 'required'],
		        ['field' => 'from_warehouse_id', 'label' => 'From Warehouse', 'rules' => 'required'],
		        ['field' => 'to_warehouse_id', 'label' => 'To Warehouse', 'rules' => 'required']);
			$this->form_validation->set_rules($this->inventorytransferfields);
			if ($this->form_validation->run() == FALSE) {
				$this->session->set_flashdata('alert', ['type'=>'error', 'message'=>'Invalid Input Data']);
				redirect('inventory/recieve_from_warehouse');
			}
			else {
				$itemId = $this->input->post('item_id');
				$toWarehouseId = $this->input->post('to_warehouse_id');
				$quantity = $this->input->post('quantity');
				// check item code exist and get existing amount for that item against that warehouse
				$existingItem = $this->Common_model->select_fields_where_like_join('inventory i', 'i.id, wi.quantity, wi.id as warehouseInventoryId',
				[
					['table'=>'warehouse_inventory wi', 'condition'=>'wi.inventory_id = i.id and wi.warehouse_id = '.$toWarehouseId, 'type'=>'left']
				],
				[
					'i.item_id' => $itemId,
				], true);
				// check if not item not exist in that warehouse than insert
				if($existingItem->warehouseInventoryId != NULL)
					$this->Common_model->update('warehouse_inventory',[
						'warehouse_id' => $toWarehouseId,
						'status' => 1,
						'inventory_id' => $existingItem->id
					],[
						'quantity' => $existingItem->quantity + $quantity,
						'updated_at' => date('Y-m-d h:i:s'),
					]);
				else { // insert item
					$existingItem->warehouseInventoryId = $this->Common_model->insert_record('warehouse_inventory',[
						'warehouse_id' => $toWarehouseId,
						'status' => 1,
						'inventory_id' => $existingItem->id,
						'quantity' => $quantity,
						'updated_at' => date('Y-m-d h:i:s'),
						'created_at' => date('Y-m-d h:i:s'),
					]);
				}
				
				$data = [
					'warehouse_inventory_id' => $existingItem->warehouseInventoryId,
					'to_user_id' => $this->session->userdata('user')->id,
					'quantity' => $this->input->post('quantity'),
					'from_warehouse_id' => $this->input->post('from_warehouse_id'),
					'to_warehouse_id' => $toWarehouseId,
					'from_user_id' => $this->input->post('from_user_id'),
					'type' => 2,
					'created_at' => date('Y-m-d h:i:s'),
				];
				$insertedId = $this->Common_model->insert_record('inventory_transfer', $data);
				if($insertedId){
					$this->session->set_flashdata('alert', ['type'=>'success', 'message'=>'Spare part received from warehouse successfully']);
					$fromWH = $this->Common_model->select_fields_where('warehouse','name', ['id'=>$this->input->post('from_warehouse_id')], true);
					$toWH   = $this->Common_model->select_fields_where('warehouse','name', ['id'=>$toWarehouseId], true);
					$activity = array('warehouse_id' =>$toWarehouseId,'model_id' => $toWarehouseId,'method' => ''.$quantity.' Spare part received from Warehouse'.$fromWH->name.' to '.$toWH->name.'', 'model_name' => 'Spare','name'=> $itemId,'detail'=> 'Spare part sent to warehouse','rout'=>'inventory/'.$existingItem->warehouseInventoryId);
					logs($activity);
					redirect('inventory');
				} else {
					$this->session->set_flashdata('alert', ['type'=>'error', 'message'=>'Error adding']);
					redirect('inventory/recieve_from_warehouse');
				}
			}
		}
		else {
			$where_in = '';
			if(!isAdministrator($this->session->userdata('user')->id)){
				$where_in = ['col'=>'id', 'val'=>getUserWareHouseIds($this->session->userdata('user')->id)];
			}
			$warehouses = $this->Common_model->select_fields_where('warehouse','*',['status'=>1], FALSE, '', '', '','','',false, $where_in);
			$data = [
				'users' => $this->Common_model->select_fields_where('user', '*', ['status'=>1]),
				'warehouses' => $warehouses,
				'inventory_types' => $this->Common_model->select_fields_where('inventory_type', '*', ['status'=>1])
			];
			$this->show('inventory/recieve_from_warehouse', $data);
		}
	}
	public function send_to_technician(){
		if($this->input->method() == 'post'){
			array_push($this->inventorytransferfields, 
		        ['field' => 'from_warehouse_id', 'label' => 'From Warehouse', 'rules' => 'required'],['field' => 'technician_id', 'label' => 'From Person', 'rules' => 'required']);
			$this->form_validation->set_rules($this->inventorytransferfields);
			if ($this->form_validation->run() == FALSE) {
				$this->session->set_flashdata('alert', ['type'=>'error', 'message'=>'Invalid Input Data']);
				redirect('inventory/send_to_technician');
			}
			else {
				$itemId = $this->input->post('item_id');
				$fromWarehouseId = $this->input->post('from_warehouse_id');
				$quantity = $this->input->post('quantity');
				// check item code exist and get existing amount for that item against that warehouse
				$existingItem = $this->Common_model->select_fields_where_like_join('inventory i', 'i.id, wi.quantity, wi.id as warehouseInventoryId',
				[
					['table'=>'warehouse_inventory wi', 'condition'=>'wi.inventory_id = i.id', 'type'=>'inner']
				],
				[
					'i.item_id' => $itemId,
					'wi.warehouse_id' => $fromWarehouseId,
					'wi.status' => 1,
				], true);
				if(!$existingItem) {
					$this->session->set_flashdata('alert', ['type'=>'error', 'message'=>'Item not exist in selected warehouse']);
					redirect('inventory/send_to_technician');
				}
				if($existingItem->quantity < $quantity) {
					$this->session->set_flashdata('alert', ['type'=>'error', 'message'=>'Not enough amount of items exist in selected warehouse']);
					redirect('inventory/send_to_warehouse');
				}
				$this->Common_model->update('warehouse_inventory',[
					'warehouse_id' => $fromWarehouseId,
					'status' => 1,
					'inventory_id' => $existingItem->id
				],[
					'quantity' => $existingItem->quantity - $quantity
				]);
				
				$data = [
					'warehouse_inventory_id' => $existingItem->warehouseInventoryId,
					'to_user_id' => $this->input->post('technician_id'),
					'quantity' => $this->input->post('quantity'),
					'from_warehouse_id' => $fromWarehouseId,
					'from_user_id' =>$this->session->userdata('user')->id ,
					'type' => 3,
					'created_at' => date('Y-m-d h:i:s'),
				];
				$insertedId = $this->Common_model->insert_record('inventory_transfer', $data);
				if($insertedId){
					$this->session->set_flashdata('alert', ['type'=>'success', 'message'=>'Spare part send to technician successfully']);
					$fromWH = $this->Common_model->select_fields_where('warehouse','name', ['id'=>$fromWarehouseId], true);
					$technician   = $this->Common_model->select_fields_where('user','username', ['id'=>$this->input->post('technician_id')], true);
					$activity = array('warehouse_id' =>$fromWarehouseId,'model_id' => $fromWarehouseId,'method' => ''.$quantity.' Spare part issued by '.$fromWH->name. ' to Technician '.$technician->username.'', 'model_name' => 'Spare','name'=> $itemId,'detail'=> 'Spare part sent to warehouse','rout'=>'inventory/'.$existingItem->warehouseInventoryId);
                    logs($activity);
					redirect('inventory');
				} else {
					$this->session->set_flashdata('alert', ['type'=>'error', 'message'=>'Error adding']);
					redirect('inventory/send_to_technician');
				}
			}
		}
		else {
			$where_in = '';
			if(!isAdministrator($this->session->userdata('user')->id)){
				$where_in = ['col'=>'id', 'val'=>getUserWareHouseIds($this->session->userdata('user')->id)];
			}
			$warehouses = $this->Common_model->select_fields_where('warehouse','*',['status'=>1], FALSE, '', '', '','','',false, $where_in);
			$data = [
				'users' => $this->Common_model->select_fields_where('user', '*', ['status'=>1]),
				'warehouses' => $warehouses,
				'inventory_types' => $this->Common_model->select_fields_where('inventory_type', '*', ['status'=>1])
			];
			$this->show('inventory/send_to_technician', $data);
		}
	}
	public function recieve_from_technician(){
		if($this->input->method() == 'post'){
			array_push($this->inventorytransferfields, 
		        ['field' => 'to_warehouse_id', 'label' => 'To Warehouse', 'rules' => 'required'],['field' => 'technician_id', 'label' => 'From Person', 'rules' => 'required']);
			$this->form_validation->set_rules($this->inventorytransferfields);
			if ($this->form_validation->run() == FALSE) {
				$this->session->set_flashdata('alert', ['type'=>'error', 'message'=>'Invalid Input Data']);
				redirect('inventory/recieve_from_technician');
			}
			else {
				$itemId = $this->input->post('item_id');
				$toWarehouseId = $this->input->post('to_warehouse_id');
				$quantity = $this->input->post('quantity');
				// check item code exist and get existing amount for that item against that warehouse
				$existingItem = $this->Common_model->select_fields_where_like_join('inventory i', 'i.id, wi.quantity, wi.id as warehouseInventoryId',
				[
					['table'=>'warehouse_inventory wi', 'condition'=>'wi.inventory_id = i.id and wi.warehouse_id = '.$toWarehouseId, 'type'=>'left']
				],
				[
					'i.item_id' => $itemId,
				], true);
				if($existingItem->warehouseInventoryId !=NULL)
					$this->Common_model->update('warehouse_inventory',[
						'warehouse_id' => $toWarehouseId,
						'status' => 1,
						'inventory_id' => $existingItem->id
					],[
						'quantity' => $existingItem->quantity + $quantity,
						'updated_at' => date('Y-m-d h:i:s')
					]);
				else { // insert item
					$existingItem->warehouseInventoryId = $this->Common_model->insert_record('warehouse_inventory',[
						'warehouse_id' => $toWarehouseId,
						'status' => 1,
						'inventory_id' => $existingItem->id,
						'quantity' => $quantity,
						'updated_at' => date('Y-m-d h:i:s'),
						'created_at' => date('Y-m-d h:i:s'),
					]);
				}
				$data = [
					'warehouse_inventory_id' => $existingItem->warehouseInventoryId,
					'from_user_id' => $this->input->post('technician_id'),
					'quantity' => $this->input->post('quantity'),
					'to_warehouse_id' => $toWarehouseId,
					'to_user_id' =>$this->session->userdata('user')->id ,
					'type' => 4,
					'created_at' => date('Y-m-d h:i:s'),
				];
				$insertedId = $this->Common_model->insert_record('inventory_transfer', $data);
				if($insertedId){
					$this->session->set_flashdata('alert', ['type'=>'success', 'message'=>'Spare part receive from technician successfully']);
					$fromWH = $this->Common_model->select_fields_where('warehouse','name', ['id'=>$toWarehouseId], true);
					$technician   = $this->Common_model->select_fields_where('user','username', ['id'=>$this->input->post('technician_id')], true);
					$activity = array('warehouse_id' =>$toWarehouseId,'model_id' => $toWarehouseId,'method' => ''.$quantity.' Spare part received from Technician '.$technician->username.' to '.$fromWH->name.'', 'model_name' => 'Spare','name'=> $itemId,'detail'=> 'Spare part sent to warehouse','rout'=>'inventory/'.$existingItem->warehouseInventoryId);
					logs($activity);
					redirect('inventory');
				} else {
					$this->session->set_flashdata('alert', ['type'=>'error', 'message'=>'Error adding']);
					redirect('inventory/recieve_from_technician');
				}
			}
		}
		else {
			$where_in = '';
			if(!isAdministrator($this->session->userdata('user')->id)){
				$where_in = ['col'=>'id', 'val'=>getUserWareHouseIds($this->session->userdata('user')->id)];
			}
			$warehouses = $this->Common_model->select_fields_where('warehouse','*',['status'=>1], FALSE, '', '', '','','',false, $where_in);
			$data = [
				'users' => $this->Common_model->select_fields_where('user', '*', ['status'=>1]),
				'warehouses' => $warehouses,
				'inventory_types' => $this->Common_model->select_fields_where('inventory_type', '*', ['status'=>1])
			];
			$this->show('inventory/recieve_from_technician', $data);
		}
	}
	public function minlevel() {
        if(isAdministrator($this->session->userdata('user')->id)) 
			$this->show('inventory/minlevelstock_listing');
	}
	public function import(){
        if(isEndUser($this->session->userdata('user')->id)) 
            return redirect('inventory');
		if($this->input->method() == 'post'){
			$repeatingItem = [];
			$itemTypeId = $this->input->post('inventory_type_id');
			if($itemTypeId == '' or empty($itemTypeId)){
				echo  json_encode(['type' => 'error', 'message' => 'Please select inventory type']);
				exit;
			}
			$warehouseId = $this->input->post('warehouse_id');
			if($warehouseId == '' or empty($warehouseId)) {
				echo json_encode(['type' => 'error', 'message' => 'Please select warehouse']);
				exit;
			}
			
			$file = $_FILES['excel_file']['tmp_name'];
			$handle = fopen($file, "r");
		    if ($file == NULL)
		    	$message = json_encode(['type' => 'error', 'message' => 'Input File is not Valid']);
		    else {
		    	try{
			    	$whItemDataToInsert = $whItemDateToUpdate = $inventorydataToInsert = $inventorydataToUpdate = [];
			    	$existingItem = $this->Common_model->select_fields_where_like_join('inventory i', 'i.item_id, wi.quantity, wi.id, i.id as inventory_id', [
			    		['table'=>'warehouse_inventory wi', 'condition'=>'wi.inventory_id = i.id and wi.warehouse_id = '.$warehouseId, 'type'=>'left']
			    	]);
			    	$existingItemIds = [];
			    	if(is_array($existingItem)) {
				    	foreach ($existingItem as $key => $value) {
				    		$existingItemIds[strval($value->item_id)] = [
				    			'quantity'=>$value->quantity,
				    			'whInventoryId'=>$value->id,
				    			'inventoryId'=>$value->inventory_id,
				    		];
				    	}
			    	}
	            	$this->load->library('excel');
			        $object = PHPExcel_IOFactory::load($file);
			        /*$highestColumn = $object->setActiveSheetIndex(0)->getHighestColumn();
			        if($highestColumn != 'E') {
			        	echo json_encode(['type' => 'error', 'message' => 'Invalid file data']);
			        	exit;
			        }*/

			        $item_serial_numbers = [];
					foreach($object->getWorksheetIterator() as $worksheet) {
					    $highestRow = $worksheet->getHighestRow();
					    $highestColumn = $worksheet->getHighestColumn();
					    for($row=2; $row<=$highestRow; $row++) {
					    	$itemId = $worksheet->getCellByColumnAndRow(0, $row)->getValue();
					    	if(empty($itemId) or $itemId == '')
					    		continue;

					    	$serial_number = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
					    	// check serial number not empty
					    	if(!empty($serial_number)){
					    		if(isset($item_serial_numbers[strval($itemId)])){
					    			if(!in_array($serial_number, $item_serial_numbers[strval($itemId)]))
					    				array_push($item_serial_numbers[strval($itemId)], $serial_number);
					    		} else 
					    			$item_serial_numbers[strval($itemId)] = [$serial_number];
					    	}

					    	$minlevel = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
					    	$qty = $worksheet->getCellByColumnAndRow(4, $row)->getValue();
					    	// item id is unique in our system , check if exist already than do plus One in the item quantity
					    	if(array_key_exists(strval($itemId), $existingItemIds)){
					    		array_push($inventorydataToUpdate, [
					    			'item_id' => $itemId,
					    			'description' => $worksheet->getCellByColumnAndRow(1, $row)->getValue(),
					    			'inventory_type_id' => $itemTypeId,
					    			'serial_number' => $worksheet->getCellByColumnAndRow(2, $row)->getValue(),
					    			'updated_at' => date('Y-m-d h:i:s'),
					    		]);
					    		$whInventoryId = $existingItemIds[strval($itemId)]['whInventoryId'];
					    		if($whInventoryId != NULL) {
					    			$existingItemIds[strval($itemId)]['quantity'] += $qty;
						    		array_push($whItemDateToUpdate, [
						    			'id' => $whInventoryId,
						    			'warehouse_id' => $warehouseId,
						    			'quantity' =>$existingItemIds[strval($itemId)]['quantity'],
						    			'updated_at' => date('Y-m-d h:i:s'),
						    		]);
					    		} else {
							    	if(array_key_exists(strval($itemId), $repeatingItem)) 
							    		$repeatingItem[strval($itemId)]['quantity'] += $qty;
							    	else {
							    		array_push($whItemDataToInsert, [
							    			'inventory_id' => $existingItemIds[strval($itemId)]['inventoryId'],
							    			'quantity' => $qty,
							    			'updated_at' => date('Y-m-d h:i:s'),
						    				'min_level' => $minlevel,
						    				'warehouse_id' => $warehouseId,
							    			'updated_at' => date('Y-m-d h:i:s'),
							    			'created_at' => date('Y-m-d h:i:s'),
							    		]);
							    		$repeatingItem[strval($itemId)] = [
							    			'quantity' => 0,
							    			'initial' => $qty,
							    			'warehouse_id' => $warehouseId
							    		];
							    	}
					    		}
					    	}
					    	else {
						    	if(array_key_exists(strval($itemId), $repeatingItem)) 
						    		$repeatingItem[strval($itemId)]['quantity'] += $qty;
						    	else {
						    		array_push($inventorydataToInsert, [
						    			'item_id' => $itemId,
						    			'description' => $worksheet->getCellByColumnAndRow(1, $row)->getValue(),
						    			'inventory_type_id' => $itemTypeId,
						    			'serial_number' => $worksheet->getCellByColumnAndRow(2, $row)->getValue(),
						    			'updated_at' => date('Y-m-d h:i:s'),
						    			'created_at' => date('Y-m-d h:i:s'),
						    		]);
						    		
						    		array_push($whItemDataToInsert, [
						    			'item_id' => $itemId,
						    			'quantity' => $qty,
					    				'min_level' => $minlevel,
					    				'warehouse_id' => $warehouseId,
						    			'updated_at' => date('Y-m-d h:i:s'),
						    			'created_at' => date('Y-m-d h:i:s'),
						    		]);
						    		$repeatingItem[strval($itemId)] = [
						    			'quantity' => 0,
						    			'initial' => $qty,
						    			'warehouse_id' => $warehouseId
						    		];
						    	}
					    	}
					    }
					}
					// remove repeating
					if(!empty($whItemDateToUpdate)){
						$repeatingItemIds = [];
						foreach($whItemDateToUpdate as $key => $whItemToUpdate) {
							if(array_key_exists($whItemToUpdate['id'], $repeatingItemIds))
								unset($whItemDateToUpdate[$repeatingItemIds[$whItemToUpdate['id']]]);
							
							$repeatingItemIds[$whItemToUpdate['id']] = $key;
						}
					}
					// insert new inventory items in bulk
					if(!empty($inventorydataToInsert)){
						$this->Common_model->insert_multiple('inventory', $inventorydataToInsert);
						$newItemIds = array_column($inventorydataToInsert, 'item_id');
						
						$newItems = $this->Common_model->select_fields_where('inventory', 'id, item_id','',FALSE,'','', '','','', false, ['col'=>'item_id', 'val'=>$newItemIds]);
						foreach($newItems as $item) {
							foreach ($whItemDataToInsert as $key => $value) {
								if(isset($value['item_id'])){
									if($item->item_id == $value['item_id']){
										$whItemDataToInsert[$key]['inventory_id'] = $item->id;
										unset($whItemDataToInsert[$key]['item_id']);
									}
								}
							}
						}
					}
					// insert new inventory warehouse items in bulk
					if(!empty($whItemDataToInsert))
						$this->Common_model->insert_multiple('warehouse_inventory', $whItemDataToInsert);
					// udpate existing inventory items in bulk
					if(!empty($inventorydataToUpdate))
						$this->Common_model->update_multiple('inventory', $inventorydataToUpdate, 'item_id');
					// insert repeating items in bulk 
					if(!empty($repeatingItem)){
						foreach ($repeatingItem as $key => $value) {
							if($value['quantity'] == 0)
								unset($repeatingItem[$key]);
						}
						if(!empty(array_keys($repeatingItem))){
							$joins = [
								['table'=>'warehouse_inventory wi', 
								'condition'=>'wi.inventory_id = i.id', 
								'type' => 'inner']
							];
							$repeatingItems = $this->Common_model->select_fields_where_like_join('inventory i', 'wi.id, i.item_id',$joins, ['wi.warehouse_id'=>$warehouseId], FALSE, '', '','','','',false, ['col'=>'i.item_id', 'val'=>array_keys($repeatingItem)]);
							foreach($repeatingItems as $item) {
								if(!empty($repeatingItem[strval($item->item_id)])) {
									array_push($whItemDateToUpdate, [
										'id' => $item->id,
										'warehouse_id' => $repeatingItem[strval($item->item_id)]['warehouse_id'],
										'quantity' => $repeatingItem[strval($item->item_id)]['quantity'] + $repeatingItem[strval($item->item_id)]['initial'],
										'updated_at' => date('Y-m-d h:i:s'),
									]);
								}
							}
						}
					}
					// update existing warehouse inventory items in bulk
					if(!empty($whItemDateToUpdate))
						$this->Common_model->update_multiple('warehouse_inventory', $whItemDateToUpdate, 'id');

					// update serial number
			      	if(!empty($item_serial_numbers)){
			      		$items = $this->Common_model->select_fields_where('inventory','id,item_id,serial_number', '', FALSE, '', '', '','','',true, ['col'=>'item_id','val'=>array_keys($item_serial_numbers)]);
	
			      		if(is_array($items) and !empty($items)){
			      			foreach($items as $key => $item){
			      				if(array_key_exists(strval($item['item_id']), $item_serial_numbers)){
			      					$existing_serials = explode(',', $item['serial_number']);
			      					$new_serials = $item_serial_numbers[strval($item['item_id'])];
			      					$final_serial = array_unique(array_merge($existing_serials,$new_serials), SORT_REGULAR);
			      					$items[$key]['serial_number'] = implode(',', $final_serial);
			      				
			      				}

			      			}
							// udpate existing inventory items serial numbers in bulk
							$this->Common_model->update_multiple('inventory', $items, 'id');
			      		}

			      	}


			      	$message = json_encode(['type' => 'success', 'message' => 'File has been imported successfully']);
			    } catch(Exception $e){
			    	$message = json_encode(['type' => 'error', 'message' => 'Error Importing']);
			    }
		    }
		    echo $message;
		} else {
			$data = [
				'inventory_types' => $this->Common_model->select_fields_where('inventory_type', '*', ['status'=>1]),
				'warehouses' => $this->Common_model->select_fields_where('warehouse', '*', ['status'=>1]),
			];
			$this->show('inventory/import', $data);
		}
	}
	public function barcode(){
		$this->load->view('barcode/index');
	}
	public function item() {
		$itemId = $this->input->post('itemId');
		$item = $this->Common_model->select_fields_where('inventory', '*', ['item_id'=>$itemId], true);
		if($item)
			echo json_encode(['item'=>$item, 'type'=>'success']);
		else 
			echo json_encode(['type'=>'error','message'=>'Item not exist with entered item code']);
	}
	public function bulk_action($actionId){
		$selected = json_decode($this->input->post('selected_checks'));
		if(!empty($selected)){
			switch ($actionId) {
				case '1':
					$this->Common_model->update('warehouse_inventory', '', ['status'=>1], ['col'=>'id', 'val'=>$selected]);
					break;
				case '2':
					$this->Common_model->update('warehouse_inventory', '', ['status'=>0], ['col'=>'id', 'val'=>$selected]);
					break;
				
				default:
					# code...
					break;
			}
			echo json_encode(['type' => 'success', 'message' => 'Selected items updated successfully']);
		} else {
			echo json_encode(['type' => 'error', 'message' => 'Please select item']);
		}
	}
}
