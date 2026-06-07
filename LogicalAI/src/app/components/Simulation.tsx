import { useState, useEffect, useRef } from "react";
import { useNavigate, useLocation } from "react-router";
import { motion, AnimatePresence } from "motion/react";
import { ArrowLeft, User, Bot, Send, BarChart3, ChevronRight } from "lucide-react";

interface Message {
  role: "user" | "ai";
  content: string;
  typing?: boolean;
}

const aiResponses: Record<string, string[]> = {
  default: [
    "흥미로운 주장이지만, 몇 가지 논리적 허점이 있습니다.\n\n첫째, 귀하의 주장은 인과관계를 상관관계와 혼동하고 있습니다. 특정 현상이 동시에 발생한다고 해서 하나가 다른 하나의 원인이 되는 것은 아닙니다.\n\n둘째, 제시하신 근거는 선택적 편향의 가능성이 있습니다. 반대되는 사례들은 고려되지 않았습니다.\n\n🔍 질문: 귀하의 논리에서 가장 핵심적인 가정은 무엇이며, 그 가정이 틀렸을 경우 어떤 결론에 도달하게 됩니까?",
    "논리적 구조는 일관되지만, 전제에 문제가 있습니다.\n\n귀하의 주장은 '모든 경우에 적용된다'는 일반화의 오류를 범하고 있습니다. 실제로는 다양한 맥락과 조건에 따라 결과가 달라질 수 있습니다.\n\n또한, 장기적 영향과 단기적 영향을 구분하지 않고 있습니다. 단기적으로는 긍정적일 수 있지만, 장기적으로는 부정적 결과를 초래할 수 있습니다.\n\n🔍 질문: 시간적 범위를 확장했을 때에도 같은 결론이 유지됩니까?",
    "설득력 있는 논점이지만, 반대 입장에서 강력한 반론이 존재합니다.\n\n1. 비용-편익 분석이 불완전합니다. 숨겨진 비용과 기회비용이 고려되지 않았습니다.\n2. 대안적 해결책에 대한 검토가 부족합니다. 귀하의 방안 외에도 더 효율적인 접근법이 있을 수 있습니다.\n3. 이해관계자 분석이 편향되어 있습니다. 특정 집단의 관점만 반영되었습니다.\n\n🔍 질문: 가장 취약한 이해관계자의 관점에서 이 주장을 재검토할 수 있습니까?",
    "논증의 형식은 갖추었으나, 실증적 근거가 부족합니다.\n\n귀하가 제시한 주장은 주로 이론적 추론에 기반하고 있으며, 실제 데이터나 사례 연구로 뒷받침되지 않습니다. 반면, 반대 입장을 지지하는 실증적 연구들이 다수 존재합니다.\n\n특히 최근 연구들은 귀하의 가정과 상반되는 결과를 보여주고 있어, 논리적 토대가 흔들릴 수 있습니다.\n\n🔍 질문: 귀하의 주장을 뒷받침하는 구체적인 실증 데이터를 제시할 수 있습니까?",
  ],
};

export default function Simulation() {
  const navigate = useNavigate();
  const location = useLocation();
  const { topic, stance, style } = (location.state as any) || {
    topic: "인공지능이 인간의 일자리를 대체해야 하는가",
    stance: "찬성",
    style: "논리적",
  };

  const [messages, setMessages] = useState<Message[]>([]);
  const [input, setInput] = useState("");
  const [isTyping, setIsTyping] = useState(false);
  const [aiIndex, setAiIndex] = useState(0);
  const scrollRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    // Initial AI message
    const initMsg: Message = {
      role: "ai",
      content: `안녕하세요. 저는 "${topic}"에 대해 ${stance === "찬성" ? "반대" : "찬성"} 입장에서 토론하겠습니다.\n\n${style} 스타일로 진행하겠습니다. 먼저 귀하의 주장을 들려주세요.`,
    };
    setTimeout(() => setMessages([initMsg]), 500);
  }, []);

  useEffect(() => {
    scrollRef.current?.scrollTo({ top: scrollRef.current.scrollHeight, behavior: "smooth" });
  }, [messages]);

  const sendMessage = () => {
    if (!input.trim() || isTyping) return;
    const userMsg: Message = { role: "user", content: input };
    setMessages((prev) => [...prev, userMsg]);
    setInput("");
    setIsTyping(true);

    const responses = aiResponses.default;
    const response = responses[aiIndex % responses.length];
    setAiIndex((prev) => prev + 1);

    // Simulate typing
    setTimeout(() => {
      setMessages((prev) => [...prev, { role: "ai", content: response }]);
      setIsTyping(false);
    }, 1500 + Math.random() * 1000);
  };

  const handleNextRebuttal = () => {
    if (isTyping) return;
    setIsTyping(true);
    const responses = aiResponses.default;
    const response = responses[aiIndex % responses.length];
    setAiIndex((prev) => prev + 1);
    setTimeout(() => {
      setMessages((prev) => [...prev, { role: "ai", content: response }]);
      setIsTyping(false);
    }, 1200);
  };

  return (
    <div className="h-screen flex flex-col">
      {/* Header */}
      <div className="flex items-center justify-between px-4 md:px-8 py-3 border-b border-white/[0.06] bg-white/[0.02]">
        <div className="flex items-center gap-3">
          <button onClick={() => navigate("/setup")} className="p-2 rounded-xl hover:bg-white/5 transition-colors cursor-pointer">
            <ArrowLeft className="w-4 h-4 text-white/50" />
          </button>
          <div>
            <h2 className="text-sm text-white/80 truncate max-w-[300px] md:max-w-none">{topic}</h2>
            <div className="flex items-center gap-2 text-xs text-white/30">
              <span className={stance === "찬성" ? "text-emerald-400" : "text-rose-400"}>나: {stance}</span>
              <span>•</span>
              <span className={stance === "찬성" ? "text-rose-400" : "text-emerald-400"}>AI: {stance === "찬성" ? "반대" : "찬성"}</span>
              <span>•</span>
              <span>{style}</span>
            </div>
          </div>
        </div>
        <motion.button
          whileHover={{ scale: 1.03 }}
          whileTap={{ scale: 0.97 }}
          onClick={() => navigate("/analysis", { state: { topic, stance, style, messages } })}
          className="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-sm cursor-pointer"
        >
          <BarChart3 className="w-3.5 h-3.5" />
          분석 보기
        </motion.button>
      </div>

      {/* Messages */}
      <div ref={scrollRef} className="flex-1 overflow-y-auto px-4 md:px-8 py-6 space-y-4">
        <AnimatePresence>
          {messages.map((msg, i) => (
            <motion.div
              key={i}
              initial={{ opacity: 0, y: 12 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.3 }}
              className={`flex gap-3 ${msg.role === "user" ? "justify-end" : "justify-start"}`}
            >
              {msg.role === "ai" && (
                <div className="w-8 h-8 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shrink-0 mt-1">
                  <Bot className="w-4 h-4 text-white" />
                </div>
              )}
              <div
                className={`max-w-[85%] md:max-w-[600px] rounded-2xl px-5 py-4 text-sm whitespace-pre-line ${
                  msg.role === "user"
                    ? "bg-indigo-600/20 border border-indigo-500/20 text-white/90"
                    : "bg-white/[0.04] border border-white/[0.06] text-white/80"
                }`}
              >
                {msg.content}
              </div>
              {msg.role === "user" && (
                <div className="w-8 h-8 rounded-xl bg-white/10 flex items-center justify-center shrink-0 mt-1">
                  <User className="w-4 h-4 text-white/60" />
                </div>
              )}
            </motion.div>
          ))}
        </AnimatePresence>

        {isTyping && (
          <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} className="flex gap-3 items-start">
            <div className="w-8 h-8 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shrink-0">
              <Bot className="w-4 h-4 text-white" />
            </div>
            <div className="bg-white/[0.04] border border-white/[0.06] rounded-2xl px-5 py-4">
              <div className="flex gap-1.5">
                {[0, 1, 2].map((i) => (
                  <motion.div
                    key={i}
                    className="w-2 h-2 rounded-full bg-indigo-400"
                    animate={{ opacity: [0.3, 1, 0.3] }}
                    transition={{ duration: 1, repeat: Infinity, delay: i * 0.2 }}
                  />
                ))}
              </div>
            </div>
          </motion.div>
        )}
      </div>

      {/* Input area */}
      <div className="border-t border-white/[0.06] px-4 md:px-8 py-4 bg-white/[0.02]">
        <div className="flex items-center gap-3 max-w-4xl mx-auto">
          <button
            onClick={handleNextRebuttal}
            disabled={isTyping || messages.length < 2}
            className="hidden md:flex items-center gap-1.5 px-4 py-3 rounded-xl border border-white/[0.08] text-sm text-white/40 hover:text-white/70 hover:border-indigo-500/30 transition-all cursor-pointer whitespace-nowrap disabled:opacity-30"
          >
            다음 반박 보기 <ChevronRight className="w-3.5 h-3.5" />
          </button>
          <div className="flex-1 flex items-center gap-2 bg-white/[0.04] border border-white/[0.08] rounded-2xl px-4 py-2 focus-within:border-indigo-500/40 transition-colors">
            <input
              value={input}
              onChange={(e) => setInput(e.target.value)}
              onKeyDown={(e) => e.key === "Enter" && sendMessage()}
              placeholder="나의 주장을 입력하세요..."
              className="flex-1 bg-transparent text-white placeholder-white/20 focus:outline-none py-1.5 text-sm"
            />
            <button
              onClick={sendMessage}
              disabled={!input.trim() || isTyping}
              className="p-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 disabled:opacity-30 transition-all cursor-pointer"
            >
              <Send className="w-4 h-4 text-white" />
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}
