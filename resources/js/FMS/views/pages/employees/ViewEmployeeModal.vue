<template>
  <VDialog
    v-model="isOpen"
    max-width="1000"
    transition="dialog-bottom-transition"
    scrollable
  >
    <VCard class="custom-modal-shadow rounded-lg">
      <VCardTitle class="d-flex align-center text-h6 font-weight-medium pa-5 bg-primary-lighten-5">
        <VIcon icon="bx-user" size="28" color="primary" class="me-3" />
        Employee Details
      </VCardTitle>

      <VCardText class="pa-6">
        <VTabs v-model="activeTab" color="primary" class="mb-6">
          <VTab value="personal">Personal Information</VTab>
          <VTab value="family">Family Background</VTab>
          <VTab value="education">Educational Background</VTab>
          <VTab value="work">Work Experience</VTab>
          <VTab value="contact">Contact Information</VTab>
        </VTabs>

        <VWindow v-model="activeTab">
          <!-- Personal Information Tab -->
          <VWindowItem value="personal">
            <VRow>
              <VCol cols="12" class="text-center mb-4">
                <VAvatar size="100" color="primary">
                  <span class="text-h4 font-weight-bold">{{ getInitials() }}</span>
                </VAvatar>
                <div class="mt-3">
                  <h3 class="text-h5">{{ getFullName() }}</h3>
                  <p class="text-body-2 text-grey-darken-1">Employee No: {{ employee?.agency_employee_no || 'N/A' }}</p>
                </div>
              </VCol>

              <VCol cols="12" md="6">
                <VTextField
                  :model-value="employee?.first_name"
                  label="First Name"
                  variant="outlined"
                  density="comfortable"
                  readonly
                />
              </VCol>

              <VCol cols="12" md="6">
                <VTextField
                  :model-value="employee?.middle_name"
                  label="Middle Name"
                  variant="outlined"
                  density="comfortable"
                  readonly
                />
              </VCol>

              <VCol cols="12" md="6">
                <VTextField
                  :model-value="employee?.last_name"
                  label="Last Name"
                  variant="outlined"
                  density="comfortable"
                  readonly
                />
              </VCol>

              <VCol cols="12" md="6">
                <VTextField
                  :model-value="employee?.name_extension"
                  label="Name Extension"
                  variant="outlined"
                  density="comfortable"
                  readonly
                />
              </VCol>

              <VCol cols="12" md="6">
                <VTextField
                  :model-value="formatDate(employee?.birth_date)"
                  label="Birth Date"
                  variant="outlined"
                  density="comfortable"
                  readonly
                />
              </VCol>

              <VCol cols="12" md="6">
                <VTextField
                  :model-value="employee?.place_of_birth"
                  label="Place of Birth"
                  variant="outlined"
                  density="comfortable"
                  readonly
                />
              </VCol>

              <VCol cols="12" md="6">
                <VTextField
                  :model-value="employee?.sex"
                  label="Sex"
                  variant="outlined"
                  density="comfortable"
                  readonly
                />
              </VCol>

              <VCol cols="12" md="6">
                <VTextField
                  :model-value="employee?.civil_status"
                  label="Civil Status"
                  variant="outlined"
                  density="comfortable"
                  readonly
                />
              </VCol>

              <VCol cols="12" md="6">
                <VTextField
                  :model-value="employee?.citizenship"
                  label="Citizenship"
                  variant="outlined"
                  density="comfortable"
                  readonly
                />
              </VCol>

              <VCol cols="12" md="6">
                <VTextField
                  :model-value="employee?.blood_type"
                  label="Blood Type"
                  variant="outlined"
                  density="comfortable"
                  readonly
                />
              </VCol>

              <VCol cols="12" md="6">
                <VTextField
                  :model-value="employee?.height"
                  label="Height"
                  variant="outlined"
                  density="comfortable"
                  readonly
                />
              </VCol>

              <VCol cols="12" md="6">
                <VTextField
                  :model-value="employee?.weight"
                  label="Weight"
                  variant="outlined"
                  density="comfortable"
                  readonly
                />
              </VCol>
            </VRow>
          </VWindowItem>

          <!-- Family Background Tab -->
          <VWindowItem value="family">
            <div v-if="employee?.family_members && employee.family_members.length > 0">
              <h6 class="text-h6 mb-4">Family Members</h6>
              <VCard
                v-for="(member, index) in employee.family_members"
                :key="index"
                class="mb-3"
                variant="outlined"
              >
                <VCardText>
                  <VRow>
                    <VCol cols="12" md="6">
                      <div class="text-caption text-grey-darken-1">Name</div>
                      <div class="font-weight-medium">{{ member.name || 'N/A' }}</div>
                    </VCol>
                    <VCol cols="12" md="6">
                      <div class="text-caption text-grey-darken-1">Relationship</div>
                      <div class="font-weight-medium">{{ member.relationship || 'N/A' }}</div>
                    </VCol>
                  </VRow>
                </VCardText>
              </VCard>
            </div>
            <div v-else class="text-center py-6 text-grey-darken-2">
              No family members recorded
            </div>
          </VWindowItem>

          <!-- Educational Background Tab -->
          <VWindowItem value="education">
            <div v-if="employee?.educations && employee.educations.length > 0">
              <h6 class="text-h6 mb-4">Educational Background</h6>
              <VCard
                v-for="(education, index) in employee.educations"
                :key="index"
                class="mb-3"
                variant="outlined"
              >
                <VCardText>
                  <VRow>
                    <VCol cols="12">
                      <div class="text-caption text-grey-darken-1">School/University</div>
                      <div class="font-weight-medium">{{ education.school || 'N/A' }}</div>
                    </VCol>
                    <VCol cols="12" md="6">
                      <div class="text-caption text-grey-darken-1">Degree/Course</div>
                      <div class="font-weight-medium">{{ education.degree || 'N/A' }}</div>
                    </VCol>
                    <VCol cols="12" md="6">
                      <div class="text-caption text-grey-darken-1">Year Graduated</div>
                      <div class="font-weight-medium">{{ education.year_graduated || 'N/A' }}</div>
                    </VCol>
                  </VRow>
                </VCardText>
              </VCard>
            </div>
            <div v-else class="text-center py-6 text-grey-darken-2">
              No educational background recorded
            </div>
          </VWindowItem>

          <!-- Work Experience Tab -->
          <VWindowItem value="work">
            <div v-if="employee?.work_experiences && employee.work_experiences.length > 0">
              <h6 class="text-h6 mb-4">Work Experience</h6>
              <VCard
                v-for="(work, index) in employee.work_experiences"
                :key="index"
                class="mb-3"
                variant="outlined"
              >
                <VCardText>
                  <VRow>
                    <VCol cols="12">
                      <div class="text-caption text-grey-darken-1">Company/Organization</div>
                      <div class="font-weight-medium">{{ work.company || 'N/A' }}</div>
                    </VCol>
                    <VCol cols="12" md="6">
                      <div class="text-caption text-grey-darken-1">Position</div>
                      <div class="font-weight-medium">{{ work.position || 'N/A' }}</div>
                    </VCol>
                    <VCol cols="12" md="6">
                      <div class="text-caption text-grey-darken-1">Period</div>
                      <div class="font-weight-medium">{{ work.period || 'N/A' }}</div>
                    </VCol>
                  </VRow>
                </VCardText>
              </VCard>
            </div>
            <div v-else class="text-center py-6 text-grey-darken-2">
              No work experience recorded
            </div>
          </VWindowItem>

          <!-- Contact Information Tab -->
          <VWindowItem value="contact">
            <VRow>
              <VCol cols="12" md="6">
                <VTextField
                  :model-value="employee?.mobile_no"
                  label="Mobile Number"
                  variant="outlined"
                  density="comfortable"
                  readonly
                />
              </VCol>

              <VCol cols="12" md="6">
                <VTextField
                  :model-value="employee?.telephone_no"
                  label="Telephone Number"
                  variant="outlined"
                  density="comfortable"
                  readonly
                />
              </VCol>

              <VCol cols="12">
                <VTextField
                  :model-value="employee?.email"
                  label="Email Address"
                  variant="outlined"
                  density="comfortable"
                  readonly
                />
              </VCol>

              <VCol cols="12">
                <VTextField
                  :model-value="employee?.residential_address"
                  label="Residential Address"
                  variant="outlined"
                  density="comfortable"
                  readonly
                />
              </VCol>

              <VCol cols="12" md="6">
                <VTextField
                  :model-value="employee?.residential_zip"
                  label="Residential ZIP Code"
                  variant="outlined"
                  density="comfortable"
                  readonly
                />
              </VCol>

              <VCol cols="12">
                <VTextField
                  :model-value="employee?.permanent_address"
                  label="Permanent Address"
                  variant="outlined"
                  density="comfortable"
                  readonly
                />
              </VCol>

              <VCol cols="12" md="6">
                <VTextField
                  :model-value="employee?.permanent_zip"
                  label="Permanent ZIP Code"
                  variant="outlined"
                  density="comfortable"
                  readonly
                />
              </VCol>
            </VRow>
          </VWindowItem>
        </VWindow>
      </VCardText>

      <VCardActions class="justify-end pa-5">
        <VBtn
          color="grey-darken-1"
          class="px-4"
          @click="close"
        >
          <VIcon icon="bx-x" class="me-1" /> Close
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  modelValue: Boolean,
  employee: {
    type: Object,
    default: null,
  },
})

const emit = defineEmits(['update:modelValue'])

const isOpen = ref(props.modelValue)
const activeTab = ref('personal')

const formatDate = (dateStr) => {
  if (!dateStr) return 'N/A';
  return new Date(dateStr).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
}

const getFullName = () => {
  if (!props.employee) return 'N/A';
  let name = props.employee.first_name || '';
  if (props.employee.middle_name) {
    name += ' ' + props.employee.middle_name;
  }
  name += ' ' + (props.employee.last_name || '');
  if (props.employee.name_extension) {
    name += ' ' + props.employee.name_extension;
  }
  return name;
}

const getInitials = () => {
  if (!props.employee) return 'N/A';
  const first = props.employee.first_name ? props.employee.first_name[0] : '';
  const last = props.employee.last_name ? props.employee.last_name[0] : '';
  return (first + last).toUpperCase();
}

watch(() => props.modelValue, (val) => {
  isOpen.value = val
  if (val) {
    activeTab.value = 'personal' // Reset to personal tab when opened
  }
})

watch(isOpen, (val) => {
  if (val !== props.modelValue) {
    emit('update:modelValue', val)
  }
})

function close() {
  isOpen.value = false
}
</script>

<style scoped>
.custom-modal-shadow {
  box-shadow: 0 12px 28px rgba(0, 0, 0, 0.18) !important;
  border-radius: 12px;
}
</style>