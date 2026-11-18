export function formatMonthYear(date) {
  if (!date) return "";
  return new Date(date).toLocaleDateString("en-US", {
    month: "short",
    year: "numeric",
    timeZone: "UTC",
  });
}
