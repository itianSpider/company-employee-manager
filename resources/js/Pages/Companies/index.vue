<template>

  <AuthenticatedLayout>
      <div class="flex justify-end mb-4 mt-5 mr-10">
          <Button @click="handleModal" class="bg-blue-500 text-white mr-5 rounded hover:bg-blue-600 transition duration-300">
              Add Company
          </Button>
      </div>
      <div class="max-w-6xl mx-auto p-4" @scroll="handleScroll" ref="scrollContainer" style="overflow-y: auto; height: 400px;">
        <Table
          :columns="columns"
          :dataSource="companies"
          rowKey="id"
          :pagination="false"
        />
      </div>

      <a-modal 
          v-model:visible ="showModal" 
          title="Add New Company"
          @cancel="handleModalCancel"
          @ok="handleSubmit"
      >
          <form @submit.prevent="handleSubmit">
              <div class="mb-4">
                  <label for="companyName" class="block text-sm font-medium text-gray-700">Company Name</label>
                  <input type="text" v-model="companyName" id="name" class="mt-1 block w-full p-2 border rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"/>
              </div>
              <div class="mb-4">
                  <label for="companyEmail" class="block text-sm font-medium text-gray-700">Company Email</label>
                  <input type="text" v-model="companyEmail" id="email" class="mt-1 block w-full p-2 border rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"/>
              </div>
              <div class="mb-4">
                  <label for="logo" class="block text-sm font-medium text-gray-700">Upload Logo</label>
                  <input type="file" @change="handleLogoUpload" id="logo" class="mt-1 block w-full p-2 border rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" />
              </div>
              <div class="mb-4">
                  <label for="companyWebsite" class="block text-sm font-medium text-gray-700">Website</label>
                  <input type="text" v-model="companyWebsite" id="website" class="mt-1 block w-full p-2 border rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"/>
              </div>
          </form>
  </a-modal>
  </AuthenticatedLayout>

</template>

<script setup>
import { ref ,onMounted  } from 'vue';
import { Table , Modal as AModal } from 'ant-design-vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from "ant-design-vue";
import { h } from 'vue';


const props = defineProps({
  companies: Array,  
});

var companies = ref([]);
var pagination = ref({
current: 1,    
pageSize: 5,  
total: 0       
});

const fetchCompanies = async (page = 1, pageSize = 5) => {
  if (pagination.value.loading) return; // Prevent multiple fetches

  try {
    pagination.value.loading = true;
    const response = await axios.get('/api/companies', {
      params: { page, pageSize },
    });

    const { data, meta } = response.data;
    companies.value = [...companies.value, ...data];
    pagination.value.total = meta.total;
    pagination.value.current = meta.current_page; 
    pagination.value.pageSize = meta.per_page;
  } catch (error) {
    console.error('Error fetching companies:', error);
  } finally {
    pagination.value.loading = false;
}
};


onMounted(() => {
  fetchCompanies(); 
});

const handleScroll = (event) => {
  const container = event.target;

  // Check if scrolled to bottom
  if (container.scrollTop + container.clientHeight >= container.scrollHeight) {
    const nextPage = pagination.value.current + 1;
    if (companies.value.length < pagination.value.total) {
      fetchCompanies(nextPage);
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
  customRender: (record) => {
      console.log("index",record.index)
    return record.index + 1; 
  },
},
{
  title: 'Logo',
  key: 'logo',
  dataIndex: 'logo',
  customRender: (logo) => {
    console.log('Logo:', logo); 
    return h('img', {
      src: `/storage/${logo.text}`, 
      alt: 'Logo',
      style: { width: '50px', height: '50px' },
    });
  },
},
{
  title: 'Name',
  dataIndex: 'name',
  key: 'name',
},
{
  title: 'Email',
  dataIndex: 'email',
  key: 'email',
},
{
title: 'Website',
dataIndex: 'website',
key: 'website',
customRender: (website) => {
  return h(
    'a',
    {
     
        href: `https://` + website.text , 
        target: '_blank',
    },
    website.text 
  );
},
},
{
  title: 'Action',
  key: 'action',
  customRender: (record) => {
    return h('div', {}, [
      h(
        Button,
        {
          onClick: () => editCompany(record.text.id),
          type: 'primary',
          class: 'mr-2',
        },
        'Edit'
      ),
      h(
        Button,
        {
          onClick: () => deleteCompany(record.text.id),
          type: 'danger',
          class: 'btn',
        },
        'Delete'
      ),
    ]);
  },
},
];



  
var showModal = ref(false);
const companyName = ref('');
const companyEmail = ref('');
const companyWebsite = ref('');
const logo = ref(null);
const isEditing = ref(false);
let editingCompanyId = ref(null);

const handleLogoUpload = (event) => {
  const file = event.target.files[0];
  if (file) {
    logo.value = file; 
  }
};

const deleteCompany = async (id) => {
  const confirmDelete = confirm("Are you sure you want to delete this company?");

  if (!confirmDelete) return;

  try {
    const response = await axios.delete(`/api/companies/${id}`);
    console.log(response);
    if (response.status === 200) {
      alert('Company deleted successfully');
      companies.value = []; 
      fetchCompanies();
    }
  } catch (error) {
    console.error('Error deleting company:', error);
    alert('There was an error deleting the company. Please try again.');
  }
};

const editCompany = (id) => {
  const companyToEdit = companies.value.find(company => company.id === id);   
  if (companyToEdit) {
    companyName.value = companyToEdit.name;
    companyEmail.value = companyToEdit.email;
    companyWebsite.value = companyToEdit.website;
    console.log('Editing:', companyToEdit); 
    
    logo.value = null;
    editingCompanyId.value = companyToEdit.id; 
    isEditing.value = true; 
    showModal.value = true; 
  }
};

const handleSubmit = async () => {
if (!validateInputs()) {
  return; 
}

const formData = new FormData();
formData.append('name', companyName.value);
formData.append('email', companyEmail.value);
formData.append('website', companyWebsite.value);

if (logo.value) {
    formData.append('logo', logo.value);
}

console.log('Submitting formData:', formData); 
try {
  let response;
  if (isEditing.value) {
    response = await axios.post(`/api/companies/${editingCompanyId.value}`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
    alert('Company updated successfully:');
  } else {
    response = await axios.post('/api/companies', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
   
    alert('Company data saved successfully:');
  }
  showModal.value = false;
  companies.value = []; 
  resetForm();
  fetchCompanies();

} catch (error) {
  console.error('Error saving company data:', error);
  alert('There was an error saving the data. Please try again.');
}
};

const validateInputs = () => {
  if (!companyName.value) {
      alert("Company Name is required.");
      return false;
  }
  if (!companyEmail.value) {
      alert("Company Email is required.");
      return false;
  }
  if (!companyWebsite.value) {
      alert("Company Website is required.");
      return false;
  }
  if (logo.value && logo.value.size > 2 * 1024 * 1024) { 
      alert("Logo file must be less than 2MB.");
      return false;
  }
  return true;
};

const handleModal = () => {
  logo.value = null;
  showModal.value = true; // Open the modal
  console.log(showModal.value);
};

const resetForm = () => {
  companyName.value = '';
  companyEmail.value = '';
  companyWebsite.value = '';
  logo.value = null;
  showModal.value = false;
  isEditing.value = false; 
  editingCompanyId.value = null; 
};

const handleModalCancel = () => {
  showModal.value = false;
  resetForm();
  pagination.value.current = 1; 
  companies.value = [];
  fetchCompanies(); 
};


// Data for table
// const dataSource = [
//     {
//         key: '1',
//         name: 'Mike',
//         age: 32,
//         address: '10 Downing Street',
//     },
//     {
//         key: '2',
//         name: 'John',
//         age: 42,
//         address: '10 Downing Street',
//     },
// ];

// const columns = [
//     {
//         title: 'Name',
//         dataIndex: 'name',
//         key: 'name',
//     },
//     {
//         title: 'Email',
//         dataIndex: 'email',
//         key: 'email',
//     },
//     {
//         title: 'Website',
//         dataIndex: 'website',
//         key: 'website',
//     },
// ];



</script>
