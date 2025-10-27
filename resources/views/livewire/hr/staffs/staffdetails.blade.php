<div>

    <x-pages.breadcrumn title="Staff Details"
        :breadcrumbs="[
        ['label' => 'Home', 'url' => route('dashboard')], 
        ['label' => 'Staff List', 'url' => route('hr.stafflist')],
        ['label' => 'Staff Details']  
        ]">
        <a href="{{ route('hr.stafflist') }}" class="btn btn-sm btn-primary">Back to List</a>
    </x-pages.breadcrumn>
    <!-- row -->
    <x-pages.empheader :age="$age" :gender="$gender" :email="$email" :getFullName="$getfullname" :editUrl="$editUrl" :employee_id="$employee_id" />
    
    
    
</div>