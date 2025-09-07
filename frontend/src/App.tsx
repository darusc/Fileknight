import { Route, Routes } from "react-router-dom";

import Login from "./pages/Login";
import Register from "./pages/Register";
import NotFoundPage from "./pages/404";
import PasswordReset from "./pages/PasswordReset";

import { Toaster } from "./components/ui/sonner";
import { ThemeProvider } from "./components/theme-provider";
import { ThemeToggle } from "./components/theme-toggle";
import { ProtectedRoute, RedirectAuthenticatedRoute } from "./components/ProtectedRoute";
import Main from "./components/layout/main";

function App() {
  return (
    <ThemeProvider defaultTheme="dark" storageKey="vite-ui-theme">
      <Toaster position="top-center" richColors />
      <ThemeToggle className="absolute top-2 right-2" />
      <Routes>
        {/* The user needs to be authenticated to access this routes
            If not authenticated, they will be redirected to /login */}
        <Route element={<ProtectedRoute />}>
          <Route element={<Main />}>
            <Route path="/home" element={<div>Home</div>} />
            <Route path="/folders" element={<div>Folders</div>} />
            <Route path="/profile" element={<div>Profile</div>} />
          </Route>
        </Route>
        {/* If the user is already authenticated, they will be redirected to /
            Doing this to avoid creating new unnecessary sessions */}
        <Route element={<RedirectAuthenticatedRoute to="/" />}>
          <Route path="/login" element={<Login />} />
          <Route path="/register" element={<Register />} />
          <Route path="/password-reset" element={<PasswordReset />} />
        </Route>
        <Route path="*" element={<NotFoundPage />} />
      </Routes>
    </ThemeProvider>
  );
}

export default App
