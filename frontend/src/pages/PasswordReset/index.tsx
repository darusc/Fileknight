import { useState } from "react"
import { useNavigate } from "react-router-dom"
import { useForm } from "react-hook-form"

import { useAuth } from "@/hooks/appContext"

import { User } from "lucide-react"
import { Input } from "@/components/ui/input"
import { Button } from "@/components/ui/button"
import { Form, FormField, FormItem, FormLabel } from "@/components/ui/form"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Banner } from "@/components/custom/banner"

export default function PasswordReset() {
  const navigate = useNavigate();
  const auth = useAuth();

  const [message, setMessage] = useState<string | null>(null);

  const form = useForm({
    defaultValues: {
      username: "",
    },
  });

  const onSubmit = async (data: { username: string }) => {
    auth.requestPasswordReset(data.username);
    setMessage("If the username exists, a password reset request has been sent to the administrator.");
  }

  return (
    <div className="flex items-center justify-center min-h-screen">
      <Card className="w-full max-w-md shadow-lg rounded-2xl">
        <CardHeader>
          <CardTitle className="text-center text-2xl font-bold">Forgot your password?</CardTitle>
        </CardHeader>
        <CardDescription className="text-center px-6">
          You can request a password reset by providing your username. You will be contacted by the administrator with further instructions.
        </CardDescription>
        <CardContent>
          <Form {...form}>
            <form className="space-y-4" onSubmit={form.handleSubmit(onSubmit)}>
              {message && <Banner variant="success">{message}</Banner>}
              <FormField
                control={form.control}
                name="username"
                rules={{ required: "Username is required" }}
                render={({ field }) =>
                  <FormItem>
                    <div className="relative">
                      <User className="absolute left-3 top-2 h-5 w-5 text-muted-foreground dark:text-muted-foreground" />
                      <Input
                        id="username"
                        type="text"
                        placeholder="username"
                        className="pl-10"
                        {...field}
                      />
                    </div>
                  </FormItem>
                }
              />

              <Button type="submit" className="w-full">Request reset</Button>

              <p className="text-center text-sm text-gray-500">
                Already have an account?{" "}
                <button
                  type="button"
                  onClick={() => navigate("/login")}
                  className="text-sm text-blue-600 dark:text-blue-400 hover:underline transition"
                >
                  Login
                </button>
              </p>

              <p className="text-center text-sm text-gray-500">
                Don't have an account?{" "}
                <button
                  type="button"
                  onClick={() => navigate("/register")}
                  className="text-sm text-blue-600 dark:text-blue-400 hover:underline transition"
                >
                  Register
                </button>
              </p>

            </form>
          </Form>
        </CardContent>
      </Card>
    </div>
  )
}