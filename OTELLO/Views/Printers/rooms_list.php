<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Manage Rooms</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Manage Rooms</li>
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
					            <h3 class="card-title"><i class="fa fa-list"></i>&nbsp; List of Rooms</h3>
				            </div>
							<div class="d-inline-block float-right">
                                <a href="<?= base_url('admin/printers/add_room'); ?>" class="btn btn-success"><i class="fa fa-plus"></i>Add room</a>
				            </div>
                        </div>
                        <div class="card-body">

                            

                            <!-- Display the list of printers here -->
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rooms as $room): ?>
                                        <tr>
                                            <td><?php echo $room->name; ?></td>
                                            <td><?php echo $room->description; ?></td>
                                            <td>

                                                <a href="<?= base_url("admin/printers/edit_room/".$room->id); ?>" class="btn btn-warning btn-xs mr5" >
                                                <i class="fa fa-edit"></i>
                                                </a>
                                                <a href="<?= base_url("admin/printers/delete_room/".$room->id); ?>" onclick="return confirm('are you sure to delete?')" class="btn btn-danger btn-xs"><i class="fa fa-remove"></i></a> 

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
