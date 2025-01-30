<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Manage Purchases</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Manage Purchases</li>
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
					            <h3 class="card-title"><i class="fa fa-list"></i>&nbsp; List of Purchases</h3>
				            </div>
							<!--<div class="d-inline-block float-right">
                                <a href="<?= base_url('admin/finance/purchase_add'); ?>" class="btn btn-success"><i class="fa fa-plus"></i>Add Purchase</a>
				            </div>-->
                        </div>
                        <div class="card-body">

                            

                            <!-- Display the list of printers here -->
                            <table class="table table-striped">
                            <thead>
                                    <tr>
                                        <th>Member</th>
                                        <th>Device Id</th>
                                        <th>Card Number</th>
                                        <th>Access Date</th>
                                        <th>End Date</th>
                                        <th>Price</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php foreach ($purchases as $purchase): ?>
                                        <tr>
                                            <td><?php echo $purchase->loyaltycard_member_firstname . " " . $purchase->loyaltycard_member_lastname ; ?></td>
                                            <td><?php echo $purchase->loyaltycard_pos_device_id; ?></td>
                                            <td><?php echo $purchase->loyaltycard_pos_cardnumber; ?></td>
                                            <td><?php echo $purchase->loyaltycard_pos_access_date; ?></td>
                                            <td><?php echo $purchase->loyaltycard_pos_end_date; ?></td>
                                            <td><?php echo round($purchase->total_price, 2) . ' €'; ?></td>
                                            <td>
                                                <a href="<?= base_url("admin/finance/purchase_detail/".$purchase->loyaltycard_pos_id); ?>" class="btn btn-success btn-xs mr5">
                                                <i class="fa fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->
