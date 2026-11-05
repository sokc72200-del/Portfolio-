const apiOrigin = import.meta.env.VITE_API_URL
  ? new URL(import.meta.env.VITE_API_URL).origin
  : window.location.origin;

export function mediaUrl(path) {
  return path ? new URL(path, apiOrigin).href : "";
}
