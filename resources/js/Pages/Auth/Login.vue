<template>
    <div class="page-wrapper">
        <main class="login-container">
            <section class="left" aria-label="Decorative Image">
                <!-- Animated Characters Section -->
                <div class="characters-wrapper">
                    <div class="branding">
                        <img src="/images/system logo.png" alt="Company Logo" class="branding-logo" />
                        <div class="branding-text">
                            <span class="branding-title">Human Resources Information System and Financial Management System</span>
                            <span class="branding-subtitle">Libacao Province</span>
                        </div>
                    </div>

                    <div class="characters-section">
                        <div class="characters-container">
                            <!-- Purple tall rectangle character -->
                            <div 
                                ref="purpleRef"
                                class="character purple"
                                :style="getPurpleStyle()"
                            >
                                <div 
                                    class="eyes"
                                    :style="getPurpleEyesStyle()"
                                >
                                    <EyeBall 
                                        :size="18" 
                                        :pupilSize="7" 
                                        :maxDistance="5" 
                                        :isBlinking="isPurpleBlinking"
                                        :forceLookX="getPurpleForceLookX()"
                                        :forceLookY="getPurpleForceLookY()"
                                    />
                                    <EyeBall 
                                        :size="18" 
                                        :pupilSize="7" 
                                        :maxDistance="5" 
                                        :isBlinking="isPurpleBlinking"
                                        :forceLookX="getPurpleForceLookX()"
                                        :forceLookY="getPurpleForceLookY()"
                                    />
                                </div>
                            </div>

                            <!-- Black tall rectangle character -->
                            <div 
                                ref="blackRef"
                                class="character black"
                                :style="getBlackStyle()"
                            >
                                <div 
                                    class="eyes"
                                    :style="getBlackEyesStyle()"
                                >
                                    <EyeBall 
                                        :size="16" 
                                        :pupilSize="6" 
                                        :maxDistance="4" 
                                        :isBlinking="isBlackBlinking"
                                        :forceLookX="getBlackForceLookX()"
                                        :forceLookY="getBlackForceLookY()"
                                    />
                                    <EyeBall 
                                        :size="16" 
                                        :pupilSize="6" 
                                        :maxDistance="4" 
                                        :isBlinking="isBlackBlinking"
                                        :forceLookX="getBlackForceLookX()"
                                        :forceLookY="getBlackForceLookY()"
                                    />
                                </div>
                            </div>

                            <!-- Orange semi-circle character -->
                            <div 
                                ref="orangeRef"
                                class="character orange"
                                :style="getOrangeStyle()"
                            >
                                <div 
                                    class="pupils"
                                    :style="getOrangeEyesStyle()"
                                >
                                    <Pupil :forceLookX="getOrangeForceLookX()" :forceLookY="getOrangeForceLookY()" />
                                    <Pupil :forceLookX="getOrangeForceLookX()" :forceLookY="getOrangeForceLookY()" />
                                </div>
                            </div>

                            <!-- Yellow tall rectangle character -->
                            <div 
                                ref="yellowRef"
                                class="character yellow"
                                :style="getYellowStyle()"
                            >
                                <div 
                                    class="pupils"
                                    :style="getYellowEyesStyle()"
                                >
                                    <Pupil :forceLookX="getYellowForceLookX()" :forceLookY="getYellowForceLookY()" />
                                    <Pupil :forceLookX="getYellowForceLookX()" :forceLookY="getYellowForceLookY()" />
                                </div>
                                <div 
                                    class="mouth"
                                    :style="getYellowMouthStyle()"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="right">
                <img src="/images/logo.png" alt="Company Logo" class="logo" />
                <h1>LOGIN</h1>

                <!-- Display errors -->
                <div v-if="Object.keys(form.errors).length" class="error-message">
                    <ul>
                        <li v-for="(errorMessages, field) in form.errors" :key="field">
                            <span v-for="(error, index) in [].concat(errorMessages)" :key="index">
                                {{ error }}
                            </span>
                        </li>
                    </ul>
                </div>

                <!-- Login form -->
                <form @submit.prevent="submit">
                    <label for="gsis_id">GSIS ID</label>
                    <input
                        type="text"
                        id="gsis_id"
                        v-model="form.gsis_id"
                        placeholder="GSIS ID"
                        required
                        autocomplete="username"
                        @focus="isTyping = true"
                        @blur="isTyping = false"
                    />

                    <label for="password">Password</label>
                    <div class="password-wrapper">
                        <input
                            :type="showPassword ? 'text' : 'password'"
                            id="password"
                            v-model="form.password"
                            placeholder="Password"
                            required
                            autocomplete="current-password"
                        />
                        <span class="toggle-password" @click="togglePassword">
                            <img src="images/icons/hide-pass.png" v-if="!showPassword" />
                            <img src="images/icons/show-pass.png" v-else />
                        </span>
                    </div>

                    <div class="forgot-password">
                        <a @click="openForgotPasswordModal" href="#">Forgot Password?</a>
                    </div>

                    <Button
                        variant="primary"
                        size="lg"
                        type="submit"
                        :disabled="form.processing"
                    >
                        <span v-if="form.processing">Logging in...</span>
                        <span v-else>Login</span>
                    </Button>
                </form>
                <ForgotPasswordModal v-model="showForgotPasswordModal" />
            </section>
        </main>

        <!-- Full Width Footer -->
        <footer class="footer-links">
            <a href="#">Privacy Policy</a>
            <a href="#">Terms of Service</a>
            <a href="#">Contact</a>
        </footer>
    </div>
</template>

<script setup>
import Button from "@/Components/Common/Button.vue";
import ForgotPasswordModal from "./ForgotPasswordModal.vue";
import EyeBall from "@/Components/Common/EyeBall.vue";
import Pupil from "@/Components/Common/Pupil.vue";
import { ref, computed, onMounted, onUnmounted, watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import axios from "axios";

const form = useForm({
  gsis_id: "",
  password: "",
});

const showPassword = ref(false);
const showForgotPasswordModal = ref(false);
const isTyping = ref(false);
const mouseX = ref(0);
const mouseY = ref(0);

// Character refs
const purpleRef = ref(null);
const blackRef = ref(null);
const orangeRef = ref(null);
const yellowRef = ref(null);

// Animation states
const isPurpleBlinking = ref(false);
const isBlackBlinking = ref(false);
const isLookingAtEachOther = ref(false);
const isPurplePeeking = ref(false);

// Mouse tracking
const handleMouseMove = (e) => {
  mouseX.value = e.clientX;
  mouseY.value = e.clientY;
};

onMounted(() => {
  window.addEventListener("mousemove", handleMouseMove);
  
  // Purple blinking
  const schedulePurpleBlink = () => {
    const interval = Math.random() * 4000 + 3000;
    setTimeout(() => {
      isPurpleBlinking.value = true;
      setTimeout(() => {
        isPurpleBlinking.value = false;
        schedulePurpleBlink();
      }, 150);
    }, interval);
  };
  schedulePurpleBlink();

  // Black blinking
  const scheduleBlackBlink = () => {
    const interval = Math.random() * 4000 + 3000;
    setTimeout(() => {
      isBlackBlinking.value = true;
      setTimeout(() => {
        isBlackBlinking.value = false;
        scheduleBlackBlink();
      }, 150);
    }, interval);
  };
  scheduleBlackBlink();
});

onUnmounted(() => {
  window.removeEventListener("mousemove", handleMouseMove);
});

// Watch for typing to trigger looking at each other
watch(isTyping, (newVal) => {
  if (newVal) {
    isLookingAtEachOther.value = true;
    setTimeout(() => {
      isLookingAtEachOther.value = false;
    }, 800);
  } else {
    isLookingAtEachOther.value = false;
  }
});

// Watch for password visibility to trigger peeking
watch([() => form.password, showPassword], ([password, visible]) => {
  if (password.length > 0 && visible) {
    const schedulePeek = () => {
      setTimeout(() => {
        isPurplePeeking.value = true;
        setTimeout(() => {
          isPurplePeeking.value = false;
        }, 800);
      }, Math.random() * 3000 + 2000);
    };
    schedulePeek();
  } else {
    isPurplePeeking.value = false;
  }
});

// Calculate character positions
const calculatePosition = (ref) => {
  if (!ref.value) return { faceX: 0, faceY: 0, bodySkew: 0 };

  const rect = ref.value.getBoundingClientRect();
  const centerX = rect.left + rect.width / 2;
  const centerY = rect.top + rect.height / 3;

  const deltaX = mouseX.value - centerX;
  const deltaY = mouseY.value - centerY;

  const faceX = Math.max(-15, Math.min(15, deltaX / 20));
  const faceY = Math.max(-10, Math.min(10, deltaY / 30));
  const bodySkew = Math.max(-6, Math.min(6, -deltaX / 120));

  return { faceX, faceY, bodySkew };
};

// Style computed properties for each character
const getPurpleStyle = () => {
  const pos = calculatePosition(purpleRef);
  const hasPassword = form.password.length > 0;
  
  let transform = '';
  if (hasPassword && showPassword.value) {
    transform = 'skewX(0deg)';
  } else if (isTyping.value || (hasPassword && !showPassword.value)) {
    transform = `skewX(${pos.bodySkew - 12}deg) translateX(40px)`;
  } else {
    transform = `skewX(${pos.bodySkew}deg)`;
  }

  return {
    height: (isTyping.value || (hasPassword && !showPassword.value)) ? '440px' : '400px',
    transform,
  };
};

const getPurpleEyesStyle = () => {
  const pos = calculatePosition(purpleRef);
  const hasPassword = form.password.length > 0;
  
  let left, top;
  if (hasPassword && showPassword.value) {
    left = '20px';
    top = '35px';
  } else if (isLookingAtEachOther.value) {
    left = '55px';
    top = '65px';
  } else {
    left = `${45 + pos.faceX}px`;
    top = `${40 + pos.faceY}px`;
  }

  return { left, top };
};

const getPurpleForceLookX = () => {
  const hasPassword = form.password.length > 0;
  if (hasPassword && showPassword.value) {
    return isPurplePeeking.value ? 4 : -4;
  }
  if (isLookingAtEachOther.value) return 3;
  return undefined;
};

const getPurpleForceLookY = () => {
  const hasPassword = form.password.length > 0;
  if (hasPassword && showPassword.value) {
    return isPurplePeeking.value ? 5 : -4;
  }
  if (isLookingAtEachOther.value) return 4;
  return undefined;
};

const getBlackStyle = () => {
  const pos = calculatePosition(blackRef);
  const hasPassword = form.password.length > 0;
  
  let transform = '';
  if (hasPassword && showPassword.value) {
    transform = 'skewX(0deg)';
  } else if (isLookingAtEachOther.value) {
    transform = `skewX(${pos.bodySkew * 1.5 + 10}deg) translateX(20px)`;
  } else if (isTyping.value || (hasPassword && !showPassword.value)) {
    transform = `skewX(${pos.bodySkew * 1.5}deg)`;
  } else {
    transform = `skewX(${pos.bodySkew}deg)`;
  }

  return { transform };
};

const getBlackEyesStyle = () => {
  const pos = calculatePosition(blackRef);
  const hasPassword = form.password.length > 0;
  
  let left, top;
  if (hasPassword && showPassword.value) {
    left = '10px';
    top = '28px';
  } else if (isLookingAtEachOther.value) {
    left = '32px';
    top = '12px';
  } else {
    left = `${26 + pos.faceX}px`;
    top = `${32 + pos.faceY}px`;
  }

  return { left, top };
};

const getBlackForceLookX = () => {
  if (form.password.length > 0 && showPassword.value) return -4;
  if (isLookingAtEachOther.value) return 0;
  return undefined;
};

const getBlackForceLookY = () => {
  if (form.password.length > 0 && showPassword.value) return -4;
  if (isLookingAtEachOther.value) return -4;
  return undefined;
};

const getOrangeStyle = () => {
  const pos = calculatePosition(orangeRef);
  const hasPassword = form.password.length > 0;
  
  return {
    transform: (hasPassword && showPassword.value) ? 'skewX(0deg)' : `skewX(${pos.bodySkew}deg)`,
  };
};

const getOrangeEyesStyle = () => {
  const pos = calculatePosition(orangeRef);
  const hasPassword = form.password.length > 0;
  
  return {
    left: (hasPassword && showPassword.value) ? '50px' : `${82 + pos.faceX}px`,
    top: (hasPassword && showPassword.value) ? '85px' : `${90 + pos.faceY}px`,
  };
};

const getOrangeForceLookX = () => {
  return (form.password.length > 0 && showPassword.value) ? -5 : undefined;
};

const getOrangeForceLookY = () => {
  return (form.password.length > 0 && showPassword.value) ? -4 : undefined;
};

const getYellowStyle = () => {
  const pos = calculatePosition(yellowRef);
  const hasPassword = form.password.length > 0;
  
  return {
    transform: (hasPassword && showPassword.value) ? 'skewX(0deg)' : `skewX(${pos.bodySkew}deg)`,
  };
};

const getYellowEyesStyle = () => {
  const pos = calculatePosition(yellowRef);
  const hasPassword = form.password.length > 0;
  
  return {
    left: (hasPassword && showPassword.value) ? '20px' : `${52 + pos.faceX}px`,
    top: (hasPassword && showPassword.value) ? '35px' : `${40 + pos.faceY}px`,
  };
};

const getYellowMouthStyle = () => {
  const pos = calculatePosition(yellowRef);
  const hasPassword = form.password.length > 0;
  
  return {
    left: (hasPassword && showPassword.value) ? '10px' : `${40 + pos.faceX}px`,
    top: (hasPassword && showPassword.value) ? '88px' : `${88 + pos.faceY}px`,
  };
};

const getYellowForceLookX = () => {
  return (form.password.length > 0 && showPassword.value) ? -5 : undefined;
};

const getYellowForceLookY = () => {
  return (form.password.length > 0 && showPassword.value) ? -4 : undefined;
};

const togglePassword = () => {
  showPassword.value = !showPassword.value;
};

const openForgotPasswordModal = () => {
  showForgotPasswordModal.value = true;
};

const submit = async () => {
  form.clearErrors();
  
  try {
    const { data } = await axios.post("/api/createToken", {
      gsis_id: form.gsis_id,
      password: form.password,
    });

    if (data.token) {
      localStorage.setItem("api_token", data.token);
      
      form.post("/login", {
        preserveState: false,
        preserveScroll: false,
      });
    }
  } catch (err) {
    console.error("Login failed:", err.response?.data || err.message);
    
    if (err.response?.data?.errors) {
      form.setError(err.response.data.errors);
    } else {
      form.setError({
        gsis_id: err.response?.data?.message || "Login failed. Please try again."
      });
    }
  }
};
</script>

<style scoped>
.page-wrapper {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.login-container {
  flex: 1;
  display: grid;
  grid-template-columns: 1fr 1fr;
}

.left {
  position: relative;
  background: linear-gradient(135deg, hsl(var(--primary) / 0.95), hsl(var(--primary) / 1), hsl(var(--primary) / 0.9));
  padding: 3rem;
  padding-bottom: 0;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  color: hsl(var(--primary-foreground));
  overflow: hidden;
}

/* Decorative elements */
.left::before {
  content: '';
  position: absolute;
  inset: 0;
  background-image: 
    repeating-linear-gradient(0deg, transparent, transparent 19px, rgba(255, 255, 255, 0.05) 19px, rgba(255, 255, 255, 0.05) 20px),
    repeating-linear-gradient(90deg, transparent, transparent 19px, rgba(255, 255, 255, 0.05) 19px, rgba(255, 255, 255, 0.05) 20px);
  pointer-events: none;
}

.left::after {
  content: '';
  position: absolute;
  top: 25%;
  right: 25%;
  width: 16rem;
  height: 16rem;
  background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
  border-radius: 50%;
  filter: blur(60px);
  pointer-events: none;
}

.characters-wrapper {
  position: relative;
  z-index: 20;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  height: 100%;
}

.branding {
  display: flex;
  align-items: center;
  gap: 1rem;
  font-size: 1rem;
  font-weight: 600;
}

.branding-logo {
  width: 4rem;
  height: 4rem;
  object-fit: contain;
  flex-shrink: 0;
}

.branding-text {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.branding-title {
  font-size: 1rem;
  font-weight: 600;
  line-height: 1.2;
}

.branding-subtitle {
  font-size: 0.75rem;
  font-weight: 400;
  line-height: 1.3;
  opacity: 0.9;
}

.characters-section {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-end;
  flex: 1;
}

.characters-container {
  position: relative;
  width: 550px;
  height: 500px;
  display: flex;
  align-items: flex-end;
  justify-content: center;
}

.character {
  position: absolute;
  bottom: 0;
  transition: all 0.7s ease-in-out;
  transform-origin: bottom center;
}

.character.purple {
  left: 70px;
  width: 180px;
  height: 400px;
  background-color: #6C3FF5;
  border-radius: 10px 10px 0 0;
  z-index: 1;
}

.character.black {
  left: 240px;
  width: 120px;
  height: 310px;
  background-color: #2D2D2D;
  border-radius: 8px 8px 0 0;
  z-index: 2;
}

.character.orange {
  left: 0;
  width: 240px;
  height: 200px;
  background-color: #FF9B6B;
  border-radius: 120px 120px 0 0;
  z-index: 3;
}

.character.yellow {
  left: 310px;
  width: 140px;
  height: 230px;
  background-color: #E8D754;
  border-radius: 70px 70px 0 0;
  z-index: 4;
}

.eyes, .pupils {
  position: absolute;
  display: flex;
  transition: all 0.7s ease-in-out;
}

.purple .eyes {
  gap: 2rem;
}

.black .eyes {
  gap: 1.5rem;
}

.orange .pupils {
  gap: 2rem;
}

.yellow .pupils {
  gap: 1.5rem;
}

.mouth {
  position: absolute;
  width: 5rem;
  height: 4px;
  background-color: #2D2D2D;
  border-radius: 9999px;
  transition: all 0.2s ease-out;
}

.footer-links {
  width: 100%;
  padding: 1.5rem 3rem;
  display: flex;
  align-items: center;
  justify-content: flex-start;
  gap: 2rem;
  font-size: 0.875rem;
  background: rgba(0, 0, 0, 0.05);
  border-top: 1px solid rgba(0, 0, 0, 0.1);
  color: rgba(0, 0, 0, 0.6);
}

.footer-links a {
  color: inherit;
  text-decoration: none;
  transition: color 0.2s;
}

.footer-links a:hover {
  color: rgba(0, 0, 0, 0.9);
  text-decoration: underline;
}

.right {
  background: hsl(var(--background));
}

@media (max-width: 1024px) {
  .login-container {
    grid-template-columns: 1fr;
  }
  
  .left {
    display: none;
  }
}
</style>