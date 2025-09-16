import { Navigate, Outlet } from "react-router-dom";
import { useAuth } from "@/hooks/appContext";

/**
 * ProtectedRoute component that checks if the user is authenticated
 * before rendering the child routes. If not authenticated,
 * it redirects to the login page.
 */
export function ProtectedRoute() {
  const auth = useAuth();

  if(!auth.isAuthenticated()) {
    return <Navigate to="/login" replace />;
  }
  
  return <Outlet />;
}

/**
 * Redirect if the user is already authenticated.
 */
export function RedirectAuthenticatedRoute({ to }: { to: string }) {
  const auth = useAuth();

  if(auth.isAuthenticated()) {
    return <Navigate to={to} replace />;
  }
  
  return <Outlet />;
}