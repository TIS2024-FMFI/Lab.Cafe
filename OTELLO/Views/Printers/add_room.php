<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Add Room</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/printers'); ?>">Manage Rooms</a></li>
                        <li class="breadcrumb-item active">Add Room</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Printer Add Form -->
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">Add New Room</h3>
                </div>
                <div class="card-body">

                <?php $this->load->view('admin/includes/_messages.php') ?>

                <?php echo form_open(base_url('admin/printers/add_room'), 'class="form-horizontal"');  ?> 
                
                <!-- Printer Name -->
                        <div class="form-group">
                            <label for="roomName">Name</label>
                            <input type="text" class="form-control" id="roomName" name="roomName" placeholder="Enter room name" required>
                        </div>

                        <!-- Printer Description -->
                        <div class="form-group">
                            <label for="roomDescription">Description</label>
                            <textarea class="form-control" id="roomDescription" name="roomDescription" placeholder="Enter room description"></textarea>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-success">Add Room</button>
                    <?php echo form_close(); ?>
                </div>
            </div>
            <!-- /.card -->
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->
