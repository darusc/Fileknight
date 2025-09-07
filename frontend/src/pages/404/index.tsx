import { useNavigate } from "react-router-dom";

import { Button } from "@/components/ui/button";
import { AlertCircle } from "lucide-react";
import { Card, CardContent } from "@/components/ui/card";

export default function NotFoundPage() {
  const navigate = useNavigate();

  return (
    <div className="flex flex-col items-center justify-center min-h-screen bg-background px-4">
      <Card className="max-w-md w-full shadow-lg rounded-2xl text-center p-8">
        <CardContent className="space-y-6">
          <AlertCircle className="mx-auto h-12 w-12 text-destructive-foreground" />

          <h1 className="text-5xl font-bold text-foreground">404</h1>

          <p className="text-muted-foreground dark:text-muted-foreground">
            Oops! The page you are looking for does not exist.
          </p>

          <Button onClick={() => navigate("/")} className="mt-4">
            Go Back Home
          </Button>
        </CardContent>
      </Card>
    </div>
  );
}