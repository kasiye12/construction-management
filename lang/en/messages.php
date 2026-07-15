<?php
return [
    'project_created' => 'Project created successfully.',
    'project_updated' => 'Project updated successfully.',
    'project_deleted' => 'Project deleted successfully.',
    'error_occurred' => 'An error occurred: :message',
    'unauthorized' => 'You do not have permission to perform this action.',
    'not_found' => 'The requested resource was not found.',
    'validation_failed' => 'Please check the form for errors.',
    'ipc_approved' => 'IPC has been approved successfully.',
    'ipc_submitted' => 'IPC has been submitted for approval.',
    'budget_exceeded' => 'Warning: Current quantity exceeds contract quantity.',
];

cat > lang/en/validation.php << 'PHP'
<?php
return [
    'project_name_required' => 'Project name is required.',
    'project_name_min' => 'Project name must be at least 3 characters.',
    'start_date_invalid' => 'Start date must be before or equal to end date.',
    'end_date_invalid' => 'End date must be after or equal to start date.',
    'amount_too_large' => 'The amount entered is too large.',
    'invalid_status' => 'The selected status is invalid.',
];
