<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Manage Purchase order N.<?php echo $purchase_id ?></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/finance/purchase_list'); ?>">Home</a></li>
                        <li class="breadcrumb-item active">Manage Purchase</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card card-info">
                        <div class="card-header">
            				<div class="d-inline-block">
					            <h3 class="card-title"><i class="fa fa-list"></i>&nbsp; List of items</h3>
				            </div>
							<div class="d-inline-block float-right">
                                <a href="<?= base_url('admin/finance/purchase_add/'.$purchase_id); ?>" class="btn btn-success"><i class="fa fa-plus"></i>Add item</a>
				            </div>
                        </div>
                        <div class="card-body">

                            

                            <!-- Display the list of printers here -->
                            <table class="table table-striped">
                            <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Count</th>
                                        <th>Price</th>
                                        <th>Tax Rate</th>
                                        <th>Created At</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php foreach ($purchases as $purchase): ?>
                                        <tr>
                                            <td><?php echo $purchase->loyaltycard_purchase_name; ?></td>
                                            <td><?php echo $purchase->loyaltycard_purchase_count; ?></td>
                                            <td><?php echo round($purchase->loyaltycard_purchase_price, 2) . ' €'; ?></td>
                                            <td><?php echo $purchase->loyaltycard_purchase_taxRate; ?></td>
                                            <td><?php echo $purchase->loyaltycard_purchase_createdAt; ?></td>
                                            <td>
                                                <a href="<?= base_url("admin/finance/purchase_edit/".$purchase->loyaltycard_purchase_id); ?>" class="btn btn-warning btn-xs mr5">
                                                <i class="fa fa-edit"></i>
                                                </a>
                                                <a href="<?= base_url("admin/finance/purchase_delete/".$purchase->loyaltycard_purchase_id); ?>" onclick="return confirm('Are you sure you want to delete this purchase?')" class="btn btn-danger btn-xs"><i class="fa fa-remove"></i></a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>                            
                        </div>
                    </div>
                </div>
            </div>
            <a href="<?= base_url('admin/finance/purchase_list'); ?>" class="btn btn-warning btn-xs mr5">
            <i class="fa fa-arrow-left"></i>
            </a>
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->
