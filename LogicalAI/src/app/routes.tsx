import { createBrowserRouter } from "react-router";
import LandingPage from "./components/LandingPage";
import DebateSetup from "./components/DebateSetup";
import Simulation from "./components/Simulation";
import AnalysisDashboard from "./components/AnalysisDashboard";

export const router = createBrowserRouter([
  { path: "/", Component: LandingPage },
  { path: "/setup", Component: DebateSetup },
  { path: "/simulation", Component: Simulation },
  { path: "/analysis", Component: AnalysisDashboard },
]);
