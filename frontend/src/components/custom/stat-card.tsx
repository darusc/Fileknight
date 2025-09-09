import { Card, CardHeader, CardContent, CardTitle } from "@/components/ui/card"

export default function StatCard({
  title,
  value,
  desc,
  icon,
}: {
  title: string
  value: string
  desc: string
  icon: React.ReactNode
}) {
  return (
    <Card>
      <CardHeader className="flex flex-row items-center justify-between pb-2">
        <CardTitle className="text-sm font-medium">{title}</CardTitle>
        {icon}
      </CardHeader>
      <CardContent>
        <div className="text-2xl font-bold">{value}</div>
        <p className="text-xs text-muted-foreground">{desc}</p>
      </CardContent>
    </Card>
  )
}