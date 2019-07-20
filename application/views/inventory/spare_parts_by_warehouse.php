<div id="main">
    <style type="text/css">
        table th{padding: 8px 10px!important;}
        .btn-group button:nth-child(2){
        display:none;
        }
    </style>
    <!-- Page Length Options -->
    <div class="row">
        <div id="breadcrumbs-wrapper" data-image="<?= base_url()?>assets/images/gallery/breadcrumb-bg.jpg">
            <!-- Search for small screen-->
            <div class="container">
                <div class="row">
                    <div class="col s12 m6 l6">
                        <h5 class="breadcrumbs-title mt-0 mb-0">Spare Parts  by Warehouse</h5>
                    </div>
                    <div class="col s12 m6 l6 right-align-md">
                        <ol class="breadcrumbs mb-0">
                            <li class="breadcrumb-item"><a href="<?= base_url('inventory')?>">Spare Parts by Warehouse </a></li>
                            <li class="breadcrumb-item"><a href="#">Listing</a></li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="col s12">
            <div class="card hidden">
                <div class="card-content"><!-- Select -->
                    <div class="row">
                        <div class="col s12">
                            <div class="card-title">
                                <div class="row">
                                    <div class="col s12 m6 l10">
                                        <h4 class="card-title">Filter By:</h4>
                                    </div>
                                </div>
                            </div>
                            <div id="view-select">
                                <div class="row filters">
                                    <div class="input-field col s12 m4 select2lib">
                                        <select name="warehouse" id="warehousefilter" class="js-example-basic-single">
                                            <option value="">All</option>
                                            <?php foreach($warehouses as $warehouse) { ?>
                                                <option value="<?=$warehouse->id?>"><?=$warehouse->name?></option>
                                            <?php } ?>
                                        </select>
                                        <label>Warehouse</label>
                                    </div>
                                    <div class="input-field col s12 m4">
                                        <input type="text" name="serial_no" />
                                        <label>Serial Number</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-content"><!-- Select -->
                    <div class="row">
                        <div class="col s12">
                            <table class="display inventoryList">
                                <thead>
                                <tr>
                                    <th>Item No.</th>
                                    <th>Serial number</th>
                                    <th>Description</th>
                                    <th>Warehouse</th>
                                    <th>Type</th>
                                    <th>Quantity</th>
                                    <th>Min Level</th>
                                </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script type="text/javascript">
    $(function () {
        //// Need To Work ON New Way Of DataTables..
        oTable ="";
        //Initialize Select2 Elements
        var usersTableSelector = $(".inventoryList");
        var url_DT = "<?=base_url();?>inventory/spare_listing";
        var aoColumns_DT = [
            {
                "mData" : "item_id"
            },
            {
                "mData" : "serial_number",
                "mRender": function(data, type, row){
                    if(data){
                        return data.split(",").join(",\n");
                        //return data.substr(0,20);
                    }else{
                        return data;
                    }
                }
            },
            {
                "mData" : "description"
            }, 
            {
                "mData" : "warehouse"
            },
            {
                "mData" : "inventory_type"
            },
            {
                "mData" : "quantity",
            },
            {
                "mData" : "min_level",
            }
        ];
        var HiddenColumnID_DT = "";
        <?php if(!isEndUser($this->session->userdata('user')->id)) { ?>
        var sDom_DT = 'Blf<"H"r>t<"F"<"row"<"col-lg-6 col-xs-12" i> <"col-lg-6 col-xs-12" p>>>';
        <?php } else { ?>
        var sDom_DT = 'lf<"H"r>t<"F"<"row"<"col-lg-6 col-xs-12" i> <"col-lg-6 col-xs-12" p>>>';
        <?php } ?>
        additional_data = [
            {'name':'warehouse', 'value': function() { return $('.filters select[name="warehouse"]').val()}},
            {'name':'serial_no', 'value': function() { return $('.filters input[name="serial_no"]').val() }},
            {'name':'min_level', 'value': function() {
                if($('.filters input[name="min_level"]').is(':checked'))
                    return true
                else return false
            }}
        ]
        var iDisplayLength = -1;
        commonDataTables(usersTableSelector,url_DT,aoColumns_DT,sDom_DT,undefined,undefined,undefined,undefined ,{
            'ColumnID' : 0,
            'SortType' : 'asc'
        },iDisplayLength , additional_data);
    });


    $(document).on('change','.filters select, input', function(e){
        oTable.fnDraw();
    });
    $(document).ready(function(e){
        $('#warehousefilter').on('select2:select', function (e) {
            oTable.fnDraw();
        });
    })
</script>
