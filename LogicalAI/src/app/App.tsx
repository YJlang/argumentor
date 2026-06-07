import { RouterProvider } from "react-router";
import { router } from "./routes";

export default function App() {
  return (
    <div className="dark min-h-screen bg-[#0a0a12] text-white" style={{ fontFamily: "'Noto Sans KR', sans-serif" }}>
      <RouterProvider router={router} />
    </div>
  );
}
