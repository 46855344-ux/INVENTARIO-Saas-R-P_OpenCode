import { useEffect, useRef, useState } from 'react';
import type { FormEvent } from 'react';
import { askAssistant, getAssistantSuggestions } from '../../services/assistant';
import { resolveApiError } from '../../services/errors';
import { Button } from '../../components/ui/Button';

interface Message {
  role: 'user' | 'assistant';
  text: string;
  status?: string;
}

export function AssistantWidget() {
  const [open, setOpen] = useState(false);
  const [messages, setMessages] = useState<Message[]>([]);
  const [suggestions, setSuggestions] = useState<string[]>([]);
  const [question, setQuestion] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const inputRef = useRef<HTMLInputElement>(null);

  useEffect(() => {
    if (!open) {
      return;
    }

    inputRef.current?.focus();

    const handleKey = (event: KeyboardEvent) => {
      if (event.key === 'Escape' && !loading) {
        setOpen(false);
      }
    };

    document.addEventListener('keydown', handleKey);

    return () => document.removeEventListener('keydown', handleKey);
  }, [open, loading]);

  const toggle = () => {
    const next = !open;
    setOpen(next);

    if (next && suggestions.length === 0) {
      getAssistantSuggestions()
        .then(setSuggestions)
        .catch(() => setSuggestions([]));
    }
  };

  const send = async (text: string) => {
    const trimmed = text.trim();

    if (trimmed === '' || loading) {
      return;
    }

    setMessages((current) => [...current, { role: 'user', text: trimmed }]);
    setQuestion('');
    setLoading(true);
    setError(null);

    try {
      const response = await askAssistant(trimmed);
      setMessages((current) => [
        ...current,
        { role: 'assistant', text: response.answer, status: response.status },
      ]);
    } catch (requestError) {
      setError(resolveApiError(requestError).message);
    } finally {
      setLoading(false);
    }
  };

  const handleSubmit = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    void send(question);
  };

  return (
    <div className="assistant-widget">
      {open && (
        <section
          className="assistant-panel"
          role="dialog"
          aria-modal="false"
          aria-label="Asistente de inventario"
        >
          <header className="assistant-header">
            <div>
              <strong>Asistente</strong>
              <span className="hint">Solo lectura</span>
            </div>
            <button
              type="button"
              className="button-link"
              aria-label="Cerrar asistente"
              onClick={() => setOpen(false)}
            >
              Cerrar
            </button>
          </header>

          <div className="assistant-messages" aria-live="polite">
            {messages.length === 0 && (
              <p className="hint">
                Consultá stock, agotados, mínimos, salidas o un resumen. No puedo modificar el inventario.
              </p>
            )}
            {messages.map((message, index) => (
              <div
                key={`${message.role}-${index}`}
                className={message.role === 'user' ? 'assistant-message is-user' : 'assistant-message'}
              >
                <p>{message.text}</p>
                {message.status && message.status !== 'ok' && (
                  <span className="hint">Estado: {message.status}</span>
                )}
              </div>
            ))}
            {loading && <p className="hint">Consultando…</p>}
          </div>

          {suggestions.length > 0 && messages.length === 0 && (
            <div className="assistant-suggestions">
              {suggestions.slice(0, 3).map((suggestion) => (
                <button
                  key={suggestion}
                  type="button"
                  className="button-link"
                  onClick={() => {
                    void send(suggestion);
                  }}
                >
                  {suggestion}
                </button>
              ))}
            </div>
          )}

          {error && (
            <p className="form-error" role="alert">
              {error}
            </p>
          )}

          <form className="assistant-form" onSubmit={handleSubmit}>
            <input
              ref={inputRef}
              value={question}
              onChange={(event) => setQuestion(event.target.value)}
              placeholder="Escribí una pregunta…"
              aria-label="Pregunta para el asistente"
              maxLength={500}
              disabled={loading}
            />
            <Button type="submit" loading={loading} disabled={question.trim() === ''}>
              Enviar
            </Button>
          </form>
        </section>
      )}

      <button
        type="button"
        className="assistant-fab"
        aria-label={open ? 'Cerrar asistente' : 'Abrir asistente'}
        aria-expanded={open}
        onClick={toggle}
      >
        {open ? '×' : '🤖'}
      </button>
    </div>
  );
}
