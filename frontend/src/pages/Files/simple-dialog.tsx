import type React from "react";
import { useForm } from "react-hook-form";

import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";

import {
  Dialog,
  DialogClose,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger
} from "@/components/ui/dialog"

import {
  Form,
  FormControl,
  FormField,
  FormItem,
  FormLabel
} from "@/components/ui/form";

export type SimpleDialogSubmitData = {
  input: string
}

type SimpleDialogProps = {
  title: string,
  description?: string,
  trigger: React.ReactNode,
  label: string,
  defaultValue?: string,
  confirm?: string,
  onSubmit: (data: SimpleDialogSubmitData) => void
}

export function SimpleDialog({
  title,
  description,
  trigger,
  label,
  defaultValue,
  confirm,
  onSubmit
}: SimpleDialogProps) {

  const form = useForm<{ input: string }>({
    defaultValues: {
      input: defaultValue
    }
  });

  return (
    <Dialog>
      <DialogTrigger asChild>{trigger}</DialogTrigger>
      <DialogContent className="sm:max-w-md">
        <DialogHeader className="mb-2">
          <DialogTitle>{title}</DialogTitle>
          <DialogDescription>{description}</DialogDescription>
        </DialogHeader>
        <Form {...form}>
          <form onSubmit={form.handleSubmit(onSubmit)}>
            <FormField
              control={form.control}
              name="input"
              render={({ field }) => (
                <FormItem>
                  <FormLabel className="text-foreground">{label}</FormLabel>
                  <FormControl>
                    <div className="relative">
                      <Input {...field} />
                    </div>
                  </FormControl>
                </FormItem>
              )}
            />
            <DialogFooter className="sm:justify-end mt-4">
              <DialogClose asChild>
                <Button variant="secondary">Cancel</Button>
              </DialogClose>
              <Button type="submit">{confirm ? confirm : "Confirm"}</Button>
            </DialogFooter>
          </form>
        </Form>
      </DialogContent>
    </Dialog>
  );
}