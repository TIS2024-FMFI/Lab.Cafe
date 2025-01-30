<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Edit Purchase</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/finance/purchase_list'); ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/finance/purchase_detail/'.$purchase->loyaltycard_purchase_pos_id); ?>">Purchase detail</a></li>
                        <li class="breadcrumb-item active">Edit Purchase</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">Edit Room Details</h3>
                </div>
                <div class="card-body">
                    <?php echo form_open(base_url('admin/finance/purchase_edit/'.$purchase->loyaltycard_purchase_id), 'class="form-horizontal"');  ?> 

                        <div class="form-group">
                            <label for="purchasePOSId">POS Id</label>
                            <input type="text" class="form-control" id="purchasePOSId" name="purchasePOSId" placeholder="Enter POS Id" value="<?= $purchase->loyaltycard_purchase_pos_id; ?>" readonly>
                        </div>

                        <div class="form-group">
                            <label for="purchaseName">Name</label>
                            <input type="text" class="form-control" id="purchaseName" name="purchaseName" placeholder="Enter product name" value="<?= $purchase->loyaltycard_purchase_name; ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="purchaseCount">Count</label>
                            <input type="text" class="form-control" id="purchaseCount" name="purchaseCount" placeholder="Enter product count" value="<?= $purchase->loyaltycard_purchase_count; ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="purchasePrice">Price</label>
                            <input type="text" class="form-control" id="purchasePrice" name="purchasePrice" placeholder="Enter product price" value="<?= $purchase->loyaltycard_purchase_price; ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="purchaseTax">Tax</label>
                            <input type="text" class="form-control" id="purchaseTax" name="purchaseTax" placeholder="Enter product tax" value="<?= $purchase->loyaltycard_purchase_taxRate; ?>" required>
                        </div>

                        <button type="submit" class="btn btn-primary">Update Purchase</button>
                    <?php echo form_close(); ?>
                </div>
            </div>

            <a href="<?= base_url('admin/finance/purchase_detail/'.$purchase->loyaltycard_purchase_pos_id); ?>" class="btn btn-warning btn-xs mr5">
                <i class="fa fa-arrow-left"></i>
            </a>
        </div>
    </section>
</div>
