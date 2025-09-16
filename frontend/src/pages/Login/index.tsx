import { useState } from "react";
import { useForm } from "react-hook-form";
import { useNavigate } from "react-router-dom";

import { useAuth } from "@/hooks/appContext";
import type { LoginCredentials } from "@/services/AuthService";

import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage } from "@/components/ui/form"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { Button } from "@/components/ui/button"
import { User, Lock, Eye, EyeOff } from "lucide-react"
import { Banner } from "@/components/custom/banner";

export default function Login() {
  const navigate = useNavigate();
  const auth = useAuth();

  const [showPassword, setShowPassword] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const form = useForm<LoginCredentials>({
    defaultValues: {
      username: "",
      password: "",
    },
  });

  const onSubmit = async (data: LoginCredentials) => {
    const success = await auth.login(data);
    if (success) {
      navigate("/");
    } else {
      setError("Invalid credentails. Please try again.");
      form.reset();
    }
  }

  return (
    <div className="flex items-center justify-center min-h-screen">
      <Card className="w-full max-w-md shadow-lg rounded-2xl">
        <CardHeader>
          <CardTitle className="text-center text-2xl font-bold">Login</CardTitle>
        </CardHeader>
        <CardContent>
          <Form {...form}>
            <form className="space-y-4" onSubmit={form.handleSubmit(onSubmit)}>
              {error && <Banner variant="error">{error}</Banner>}
              <FormField
                control={form.control}
                name="username"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel className="text-foreground">Username</FormLabel>
                    <FormControl>
                      <div className="relative">
                        <User className="absolute left-3 top-2.5 h-5 w-5 text-muted-foreground dark:text-muted-foreground" />
                        <Input
                          {...field}
                          placeholder="username"
                          className="pl-10"
                          aria-label="Username"
                        />
                      </div>
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />

              <FormField
                control={form.control}
                name="password"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel className="text-foreground">Password</FormLabel>
                    <FormControl>
                      <div className="relative">
                        <Lock className="absolute left-3 top-2.5 h-5 w-5 text-muted-foreground dark:text-muted-foreground" />
                        <Input
                          {...field}
                          type={showPassword ? "text" : "password"}
                          placeholder="••••••••"
                          className="pl-10 pr-10"
                          aria-label="Password"
                        />
                        <button
                          type="button"
                          onClick={() => setShowPassword((prev) => !prev)}
                          className="absolute right-3 top-2.5 text-muted-foreground dark:text-muted-foreground hover:text-foreground dark:hover:text-foreground transition"
                        >
                          {showPassword ? <EyeOff className="h-5 w-5" /> : <Eye className="h-5 w-5" />}
                        </button>
                      </div>
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />

              <Button type="submit" className="w-full">Sign In</Button>

              <p className="text-center text-sm text-muted-foreground dark:text-muted-foreground">
                Don't have an account?{" "}
                <button
                  type="button"
                  onClick={() => navigate("/register")}
                  className="text-blue-600 dark:text-blue-400 hover:underline transition"
                >
                  Register
                </button>
              </p>

              <div className="text-right">
                <button
                  type="button"
                  onClick={() => navigate("/password-reset")}
                  className="text-sm text-blue-600 dark:text-blue-400 hover:underline transition"
                >
                  Forgot password?
                </button>
              </div>
            </form>
          </Form>
        </CardContent>
      </Card>
    </div>
  )
}