import { useState } from "react"
import { useForm } from "react-hook-form"
import { useNavigate } from "react-router-dom"

import { useAuth } from "@/hooks/appContext"
import type { RegisterCredentials } from "@/services/AuthService"

import { toast } from "sonner"
import { Input } from "@/components/ui/input"
import { Button } from "@/components/ui/button"
import { User, Key, Lock, Eye, EyeOff } from "lucide-react"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage } from "@/components/ui/form"
import { Banner } from "@/components/custom/banner"

export default function Register() {
  const navigate = useNavigate();
  const auth = useAuth();

  const [showPassword, setShowPassword] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const form = useForm<RegisterCredentials>({
    defaultValues: {
      username: "",
      token: "",
      password: "",
      confirmPassword: ""
    }
  });

  const onSubmit = async (data: RegisterCredentials) => {
    try {
      await auth.register(data);
      // If registration is successful, display a
      // a toast message and redirect to login page
      toast("Registration successful! You can now log in.");
      navigate("/login");
    } catch (e: any) {
      // Display error message returned from the server,
      // and reset the form fields
      setError(e.message);
      form.reset({
        username: data.username,
      });
    }
  }

  return (
    <div className="flex items-center justify-center min-h-screen">
      <Card className="w-full max-w-md shadow-lg rounded-2xl">
        <CardHeader>
          <CardTitle className="text-center text-2xl font-bold">Register</CardTitle>
        </CardHeader>
        <CardContent>
          <Form {...form}>
            <form className="space-y-4" onSubmit={form.handleSubmit(onSubmit)}>
              {error && <Banner variant="error">{error}</Banner>}
              <FormField
                control={form.control}
                name="username"
                rules={{ required: "Username is required" }}
                render={({ field }) => (
                  <FormItem>
                    <FormLabel className="text-foreground">Username</FormLabel>
                    <FormControl>
                      <div className="relative">
                        <User className="absolute left-3 top-2 h-5 w-5 text-muted-foreground dark:text-muted-foreground" />
                        <Input
                          id="username"
                          placeholder="username"
                          className="pl-10"
                          {...field}
                        />
                      </div>
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />

              <FormField
                control={form.control}
                name="token"
                rules={{ required: "Token is required" }}
                render={({ field }) => (
                  <FormItem>
                    <FormLabel className="text-foreground">Token</FormLabel>
                    <FormControl>
                      <div className="relative">
                        <Key className="absolute left-3 top-2 h-5 w-5 text-muted-foreground dark:text-muted-foreground" />
                        <Input
                          id="token"
                          placeholder="token"
                          className="pl-10"
                          {...field}
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
                rules={{
                  required: "Password is required",
                  // minLength: {
                  //   value: 8,
                  //   message: "Password must be at least 8 characters long",
                  // },
                  // pattern: {
                  //   value: /^(?=.*[A-Za-z])(?=.*\d)(?=.*[^A-Za-z\d]).+$/,
                  //   message: "Password must include a letter, a number, and a symbol",
                  // },
                }}
                render={({ field }) => (
                  <FormItem>
                    <FormLabel className="text-foreground">Password</FormLabel>
                    <FormControl>
                      <div className="relative">
                        <Lock className="absolute left-3 top-2 h-5 w-5 text-muted-foreground dark:text-muted-foreground" />
                        <Input
                          id="password"
                          type={showPassword ? "text" : "password"}
                          placeholder="••••••••"
                          className="pl-10 pr-10"
                          {...field}
                        />
                        <button
                          type="button"
                          onClick={() => setShowPassword((prev) => !prev)}
                          className="absolute right-3 top-2.5 text-gray-500 hover:text-gray-700"
                        >
                          {showPassword ? <EyeOff className="h-5 w-5" /> : <Eye className="h-5 w-5" />}
                        </button>
                      </div>
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />

              <FormField
                control={form.control}
                name="confirmPassword"
                rules={{
                  required: "Please confirm your password",
                  validate: (value) => value === form.getValues("password") || "Passwords do not match",
                }}
                render={({ field }) => (
                  <FormItem>
                    <FormLabel className="text-foreground">Confirm Password</FormLabel>
                    <FormControl>
                      <div className="relative">
                        <Lock className="absolute left-3 top-2 h-5 w-5 text-muted-foreground dark:text-muted-foreground" />
                        <Input
                          id="confirmPassword"
                          type={showPassword ? "text" : "password"}
                          placeholder="••••••••"
                          className="pl-10 pr-10"
                          {...field}
                        />
                        <button
                          type="button"
                          onClick={() => setShowPassword((prev) => !prev)}
                          className="absolute right-3 top-2.5 text-gray-500 hover:text-gray-700"
                        >
                          {showPassword ? <EyeOff className="h-5 w-5" /> : <Eye className="h-5 w-5" />}
                        </button>
                      </div>
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />

              <Button type="submit" className="w-full">Register</Button>

              <p className="text-center text-sm text-muted-foreground dark:text-muted-foreground">
                Already have an account?{" "}
                <button
                  type="button"
                  onClick={() => navigate("/login")}
                  className="text-blue-600 dark:text-blue-400 hover:underline transition"
                >
                  Login
                </button>
              </p>
            </form>
          </Form>
        </CardContent>
      </Card>
    </div>
  )
}