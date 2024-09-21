<template>
    <AuthenticatedLayout>
      <div class="flex justify-end mb-4 mt-5 mr-10">
        <Button @click="handleModal" class="bg-blue-500 text-white mr-5 rounded hover:bg-blue-600 transition duration-300">
          Add Employee
        </Button>
      </div>
      <div class="max-w-6xl mx-auto p-4" @scroll="handleScroll" ref="scrollContainer" style="overflow-y: auto; height: 400px;">
        <Table
          :columns="columns"
          :dataSource="employees"
          rowKey="id"
          :pagination="false"
        />
      </div>
  
      <a-modal 
        v-model:visible="showModal" 
        title="Add New Employee"
        @cancel="handleModalCancel"
        @ok="handleSubmit"
        >
        <form @submit.prevent="handleSubmit">
          <div class="mb-4">
            <label for="firstName" class="block text-sm font-medium text-gray-700">First Name</label>
            <input type="text" v-model="firstName" id="firsName" class="mt-1 block w-full p-2 border rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"/>
          </div>
          <div class="mb-4">
            <label for="lastName" class="block text-sm font-medium text-gray-700">Last Name</label>
            <input type="text" v-model="lastName" id="lastName" class="mt-1 block w-full p-2 border rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"/>
          </div>
          <div class="mb-4">
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input type="text" v-model="email" id="email" class="mt-1 block w-full p-2 border rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"/>
          </div>
          <div class="mb-4">
            <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
            <input type="text" v-model="phone" id="phone" class="mt-1 block w-full p-2 border rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"/>
          </div>
          <div class="mb-4">
            <label for="company" class="block text-sm font-medium text-gray-700">Company</label>
            <select v-model="selectedCompany" id="company" class="mt-1 block w-full p-2 border rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
              <option v-for="company in companies" :key="company.id" :value="company.id">{{ company.name }}</option>
            </select>
          </div>
        </form>
        <div v-if="isLoading" class="flex justify-center items-center" style="height: 100px;">
            <Spin tip="Please Wait..." />
        </div>
      </a-modal>

      <a-modal
        v-model:visible="showCompanyModal"
        title="Company Details"
        @cancel="handleCompanyModalCancel"
        @ok="handleCompanyModalCancel"
        >
        <div v-if="selectedCompany">
            <p><strong>Name:</strong> {{ selectedCompany.name }}</p>
            <p><strong>Email:</strong> {{ selectedCompany.email }}</p>
            <p><strong>Website:</strong> <a :href="selectedCompany.website" target="_blank">{{ selectedCompany.website }}</a></p>
            <p><strong>Phone:</strong> {{ selectedCompany.phone }}</p>
        </div>
      </a-modal>

    </AuthenticatedLayout>
  </template>
  
  <script setup>
  import { ref, onMounted , h } from 'vue';
  import { Table, Modal as AModal, Button , Spin  } from 'ant-design-vue';
  import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
  
  const props = defineProps({
    companies: Array,  
  });
  
  var employees = ref([]);
  var pagination = ref({
    current: 1,    
    pageSize: 5,  
    total: 0       
  });
  
  const fetchEmployees = async (page = 1, pageSize = 5) => {
    if (pagination.value.loading) return; // Prevent multiple fetches
  
    try {
      pagination.value.loading = true;
      const response = await axios.get('/api/employees', {
        params: { page, pageSize },
      });
  
      const { data, meta } = response.data;
      employees.value = [...employees.value, ...data]; // Append new data
      pagination.value.total = meta.total;
      pagination.value.current = meta.current_page;
      pagination.value.pageSize = meta.per_page;
    } catch (error) {
      console.error('Error fetching employees:', error);
    } finally {
      pagination.value.loading = false;
    }
  };
  
  onMounted(() => {
    fetchEmployees(); 
  });
  
  const handleScroll = (event) => {
    const container = event.target;
  
    if (container.scrollTop + container.clientHeight >= container.scrollHeight) {
      const nextPage = pagination.value.current + 1;
  
      if (employees.value.length < pagination.value.total) {
        fetchEmployees(nextPage);
        pagination.value.current = nextPage;
      } else {
        console.log('No more records to fetch');
      }
    }
  };
  
  const columns = [
    {
      title: 'Index',
      key: 'index',
      customRender: (record) => record.index + 1,
    },
    {
      title: 'First Name',
      dataIndex: 'first_name',
      key: 'first_name',
    },
    {
      title: 'Last Name',
      dataIndex: 'last_name',
      key: 'last_name',
    },
    {
        title: 'Company',
        dataIndex: 'company_id',
        key: 'company_id',
        customRender: (record) => {
        const company = props.companies.find(c => c.id == record.text);
        return company
            ? h('a', {
                onClick: () => showCompanyDetails(company),
                class: 'text-blue-500 hover:underline cursor-pointer',
            }, company.name)
            : 'N/A'; // Display company name as clickable link, or 'N/A' if not found
        }
    },

    {
      title: 'Email',
      dataIndex: 'email',
      key: 'email',
    },
    {
      title: 'Phone',
      dataIndex: 'phone',
      key: 'phone',
    },
    {
        title: 'Action',
        key: 'action',
        customRender: (record) => {
            return h('div', {}, [
            h(Button, {
                onClick: () => editEmployee(record.text.id),
                type: 'primary',
                class: 'mr-2',
            }, 'Edit'),
            h(Button, {
                onClick: () => deleteEmployee(record.text.id),
                type: 'danger', 
            }, 'Delete'),
            ]);
        },
        }
  ];

  var showCompanyModal = ref(false);
    // Store the company details to be displayed in the modal

    const showCompanyDetails = (company) => {
        selectedCompany.value = company; 
        showCompanyModal.value = true;   
    };

    const handleCompanyModalCancel = () => {
    showCompanyModal.value = false;
    selectedCompany.value = null; // Clear the selected company when modal is closed
    };
  
  var showModal = ref(false);
  const firstName = ref('');
  const lastName = ref('');
  const email = ref('');
  const phone = ref('');
  const selectedCompany = ref(null);
  const isEditing = ref(false);
  let editingEmployeeId = ref(null);
  const isLoading = ref(false);
  
  const handleModal = () => {
    showModal.value = true; // Open the modal
  };
  
  const handleModalCancel = () => {
    showModal.value = false;
    resetForm();
  };
  
  const resetForm = () => {
    firstName.value = '';
    lastName.value = '';
    email.value = '';
    phone.value = '';
    selectedCompany.value = null;
    isEditing.value = false; 
    editingEmployeeId.value = null; 
  };
  
  const deleteEmployee = async (id) => {
    const confirmDelete = confirm("Are you sure you want to delete this employee?");
    
    if (!confirmDelete) return;
  
    try {
      const response = await axios.delete(`/api/employees/${id}`);
      console.log(response);
      if (response.status === 200) {
        alert('Employee deleted successfully');
        employees.value = []; 
        fetchEmployees();
      }
    } catch (error) {
      console.error('Error deleting employee:', error);
      alert('There was an error deleting the employee. Please try again.');
    }
  };
  
  const editEmployee = (id) => {
    const employeeToEdit = employees.value.find(employee => employee.id === id);   
    console.log("Edit" , employeeToEdit)
    if (employeeToEdit) {
      editingEmployeeId.value = id;
      firstName.value = employeeToEdit.first_name;
      lastName.value = employeeToEdit.last_name;
      email.value = employeeToEdit.email;
      phone.value = employeeToEdit.phone;
      selectedCompany.value = employeeToEdit.company_id; // Assuming company_id exists
      isEditing.value = true; 
      showModal.value = true; 
    }
  };
  
  const handleSubmit = async () => {
    if (!validateInputs()) {
        return; 
    }
    isLoading.value = true;
    const formData = {
        first_name: firstName.value,
        last_name: lastName.value,
        email: email.value,
        phone: phone.value,
        company_id: selectedCompany.value, 
    };

    try {
        let response;
        if (isEditing.value) {
        // Update existing employee
            response = await axios.post(`/api/employees/${editingEmployeeId.value}`, formData);
            alert('Employee updated successfully:');
        } else {
        // Create new employee
            response = await axios.post('/api/employees', formData);
            alert('Employee data saved successfully:');
        }
        
        showModal.value = false; 
        resetForm();  
        employees.value = [];
        fetchEmployees(); 

    } catch (error) {
        console.error('Error saving employee data:', error);
        alert('There was an error saving the data. Please try again.');
    }finally {
        isLoading.value = false; // End loading
    }
    };

    const validateInputs = () => {
    if (!firstName.value) {
        alert("First Name is required.");
        return false;
    }
    if (!lastName.value) {
        alert("Last Name is required.");
        return false;
    }
    if (!email.value) {
        alert("Email is required.");
        return false;
    }
    if (!phone.value) {
        alert("Phone is required.");
        return false;
    }
    return true;
    };
  
  
  </script>
  