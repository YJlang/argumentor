import { useState } from "react";
import { useNavigate } from "react-router";
import { motion } from "motion/react";
import { ArrowLeft, ArrowRight, MessageSquare, Zap, GraduationCap, Swords } from "lucide-react";

const stances = [
  { value: "찬성", color: "from-emerald-500 to-teal-500", bg: "bg-emerald-500/10 border-emerald-500/30" },
  { value: "반대", color: "from-rose-500 to-red-500", bg: "bg-rose-500/10 border-rose-500/30" },
];

const styles = [
  { value: "논리적", icon: MessageSquare, desc: "체계적이고 구조화된 반박" },
  { value: "공격적", icon: Swords, desc: "날카롭고 직접적인 반박" },
  { value: "학술적", icon: GraduationCap, desc: "학문적 근거 기반 반박" },
];

const sampleTopics = [
  "인공지능이 인간의 일자리를 대체해야 하는가",
  "사형제도는 폐지되어야 하는가",
  "기본소득제는 도입되어야 하는가",
  "소셜 미디어는 사회에 해로운가",
];

export default function DebateSetup() {
  const navigate = useNavigate();
  const [topic, setTopic] = useState("");
  const [stance, setStance] = useState("");
  const [style, setStyle] = useState("논리적");

  const canStart = topic.trim() && stance;

  const handleStart = () => {
    if (!canStart) return;
    navigate("/simulation", { state: { topic, stance, style } });
  };

  return (
    <div className="min-h-screen flex flex-col">
      <nav className="flex items-center gap-3 px-6 py-4 md:px-12">
        <button onClick={() => navigate("/")} className="p-2 rounded-xl hover:bg-white/5 transition-colors cursor-pointer">
          <ArrowLeft className="w-5 h-5 text-white/50" />
        </button>
        <span className="text-white/50 text-sm">토론 설정</span>
      </nav>

      <div className="flex-1 flex items-center justify-center px-6 py-8">
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.5 }}
          className="w-full max-w-2xl space-y-8"
        >
          <div className="text-center mb-10">
            <h1 className="text-3xl bg-gradient-to-r from-white to-white/70 bg-clip-text text-transparent mb-2">토론 준비</h1>
            <p className="text-white/40 text-sm">주제와 입장을 선택하고 AI와 토론을 시작하세요</p>
          </div>

          {/* Topic */}
          <div className="space-y-3">
            <label className="text-sm text-white/60">토론 주제 입력</label>
            <input
              value={topic}
              onChange={(e) => setTopic(e.target.value)}
              placeholder="예: 인공지능이 인간의 일자리를 대체해야 하는가"
              className="w-full px-5 py-4 rounded-2xl bg-white/[0.04] border border-white/[0.08] text-white placeholder-white/20 focus:outline-none focus:border-indigo-500/50 focus:bg-white/[0.06] transition-all"
            />
            <div className="flex flex-wrap gap-2 mt-2">
              {sampleTopics.map((t) => (
                <button
                  key={t}
                  onClick={() => setTopic(t)}
                  className="text-xs px-3 py-1.5 rounded-full bg-white/[0.04] border border-white/[0.06] text-white/40 hover:text-white/70 hover:border-indigo-500/30 transition-all cursor-pointer"
                >
                  {t}
                </button>
              ))}
            </div>
          </div>

          {/* Stance */}
          <div className="space-y-3">
            <label className="text-sm text-white/60">나의 입장 선택</label>
            <div className="grid grid-cols-2 gap-4">
              {stances.map((s) => (
                <motion.button
                  key={s.value}
                  whileHover={{ scale: 1.02 }}
                  whileTap={{ scale: 0.98 }}
                  onClick={() => setStance(s.value)}
                  className={`p-5 rounded-2xl border-2 transition-all cursor-pointer ${
                    stance === s.value
                      ? s.bg
                      : "bg-white/[0.02] border-white/[0.06] hover:border-white/[0.12]"
                  }`}
                >
                  <span className={`text-lg ${stance === s.value ? "text-white" : "text-white/50"}`}>{s.value}</span>
                </motion.button>
              ))}
            </div>
          </div>

          {/* Style */}
          <div className="space-y-3">
            <label className="text-sm text-white/60">토론 스타일 선택 <span className="text-white/30">(선택)</span></label>
            <div className="grid grid-cols-3 gap-3">
              {styles.map((s) => (
                <motion.button
                  key={s.value}
                  whileHover={{ scale: 1.02 }}
                  whileTap={{ scale: 0.98 }}
                  onClick={() => setStyle(s.value)}
                  className={`p-4 rounded-2xl border text-left transition-all cursor-pointer ${
                    style === s.value
                      ? "bg-indigo-500/10 border-indigo-500/40"
                      : "bg-white/[0.02] border-white/[0.06] hover:border-white/[0.12]"
                  }`}
                >
                  <s.icon className={`w-5 h-5 mb-2 ${style === s.value ? "text-indigo-400" : "text-white/30"}`} />
                  <div className={`text-sm ${style === s.value ? "text-white" : "text-white/50"}`}>{s.value}</div>
                  <div className="text-xs text-white/30 mt-1">{s.desc}</div>
                </motion.button>
              ))}
            </div>
          </div>

          {/* CTA */}
          <motion.button
            whileHover={{ scale: canStart ? 1.02 : 1 }}
            whileTap={{ scale: canStart ? 0.98 : 1 }}
            onClick={handleStart}
            disabled={!canStart}
            className={`w-full py-4 rounded-2xl flex items-center justify-center gap-2 transition-all cursor-pointer ${
              canStart
                ? "bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-500/25"
                : "bg-white/[0.04] text-white/20 cursor-not-allowed"
            }`}
          >
            <Zap className="w-4 h-4" />
            AI 토론 시작
            <ArrowRight className="w-4 h-4" />
          </motion.button>
        </motion.div>
      </div>
    </div>
  );
}
