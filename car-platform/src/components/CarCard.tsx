'use client'

import { useState } from 'react'
import { Car, FuelType, TransmissionType, CarType } from '@prisma/client'
import { Phone, Mail, MapPin, Fuel, Calendar, Gauge, Palette, MessageCircle } from 'lucide-react'
import { useAuth } from '@/contexts/AuthContext'

interface CarWithAgency extends Car {
  agency: {
    id: string
    name: string
    phone: string
    email: string
    address: string
  }
}

interface CarCardProps {
  car: CarWithAgency
}

export default function CarCard({ car }: CarCardProps) {
  const [showContact, setShowContact] = useState(false)
  const [showMessageForm, setShowMessageForm] = useState(false)
  const [message, setMessage] = useState('')
  const [loading, setLoading] = useState(false)
  const [messageSent, setMessageSent] = useState(false)
  const { user, token } = useAuth()

  const formatPrice = (price: number) => {
    return new Intl.NumberFormat('fr-FR', {
      style: 'currency',
      currency: 'EUR'
    }).format(price)
  }

  const getFuelLabel = (fuel: FuelType) => {
    const labels = {
      ESSENCE: 'Essence',
      DIESEL: 'Diesel',
      HYBRIDE: 'Hybride',
      ELECTRIQUE: 'Électrique',
      GPL: 'GPL'
    }
    return labels[fuel]
  }

  const getTransmissionLabel = (transmission: TransmissionType) => {
    return transmission === 'MANUELLE' ? 'Manuelle' : 'Automatique'
  }

  const getTypeLabel = (type: CarType) => {
    return type === 'VENTE' ? 'À vendre' : 'À louer'
  }

  const sendMessage = async () => {
    if (!user || !token || !message.trim()) return

    setLoading(true)
    try {
      const response = await fetch('/api/messages', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify({
          content: message,
          agencyId: car.agency.id,
          carId: car.id
        })
      })

      if (response.ok) {
        setMessageSent(true)
        setMessage('')
        setTimeout(() => {
          setShowMessageForm(false)
          setMessageSent(false)
        }, 2000)
      }
    } catch (error) {
      console.error('Erreur lors de l\'envoi du message:', error)
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
      {/* Image placeholder - TODO: implement actual image display */}
      <div className="h-48 bg-gray-300 flex items-center justify-center">
        <span className="text-gray-500">Photo de la voiture</span>
      </div>

      <div className="p-4">
        <div className="flex justify-between items-start mb-2">
          <h3 className="text-lg font-semibold text-gray-900">
            {car.brand} {car.model}
          </h3>
          <span className={`px-2 py-1 rounded text-xs font-medium ${
            car.type === 'VENTE' 
              ? 'bg-green-100 text-green-800' 
              : 'bg-blue-100 text-blue-800'
          }`}>
            {getTypeLabel(car.type)}
          </span>
        </div>

        <div className="text-2xl font-bold text-blue-600 mb-3">
          {formatPrice(car.price)}
          {car.type === 'LOCATION' && <span className="text-sm text-gray-500">/jour</span>}
        </div>

        <div className="grid grid-cols-2 gap-2 text-sm text-gray-600 mb-4">
          <div className="flex items-center space-x-1">
            <Calendar className="h-4 w-4" />
            <span>{car.year}</span>
          </div>
          <div className="flex items-center space-x-1">
            <Gauge className="h-4 w-4" />
            <span>{car.mileage.toLocaleString()} km</span>
          </div>
          <div className="flex items-center space-x-1">
            <Fuel className="h-4 w-4" />
            <span>{getFuelLabel(car.fuel)}</span>
          </div>
          <div className="flex items-center space-x-1">
            <Palette className="h-4 w-4" />
            <span>{car.color}</span>
          </div>
        </div>

        <div className="text-sm text-gray-600 mb-3">
          <span className="font-medium">Transmission:</span> {getTransmissionLabel(car.transmission)}
        </div>

        {car.description && (
          <p className="text-sm text-gray-600 mb-4 line-clamp-2">
            {car.description}
          </p>
        )}

        <div className="border-t pt-3">
          <div className="flex justify-between items-center">
            <div>
              <p className="text-sm font-medium text-gray-900">{car.agency.name}</p>
              <p className="text-xs text-gray-500">{car.agency.address}</p>
            </div>
            <div className="flex space-x-2">
              {user && user.role === 'CLIENT' && (
                <button
                  onClick={() => setShowMessageForm(!showMessageForm)}
                  className="bg-green-600 text-white px-3 py-2 rounded text-sm hover:bg-green-700 transition-colors flex items-center space-x-1"
                >
                  <MessageCircle className="h-4 w-4" />
                  <span>Message</span>
                </button>
              )}
              <button
                onClick={() => setShowContact(!showContact)}
                className="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700 transition-colors"
              >
                Contacter
              </button>
            </div>
          </div>

          {showMessageForm && user && user.role === 'CLIENT' && (
            <div className="mt-3 pt-3 border-t bg-blue-50 -mx-4 px-4 pb-4">
              {messageSent ? (
                <div className="text-center py-4">
                  <div className="text-green-600 font-medium mb-2">Message envoyé !</div>
                  <p className="text-sm text-gray-600">L'agence vous contactera bientôt.</p>
                </div>
              ) : (
                <div className="space-y-3">
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                      Votre message à {car.agency.name}
                    </label>
                    <textarea
                      value={message}
                      onChange={(e) => setMessage(e.target.value)}
                      rows={3}
                      className="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      placeholder={`Je suis intéressé par cette ${car.brand} ${car.model}...`}
                    />
                  </div>
                  <div className="flex space-x-2">
                    <button
                      onClick={() => setShowMessageForm(false)}
                      className="flex-1 px-3 py-2 border border-gray-300 rounded text-sm hover:bg-gray-50"
                    >
                      Annuler
                    </button>
                    <button
                      onClick={sendMessage}
                      disabled={loading || !message.trim()}
                      className="flex-1 px-3 py-2 bg-green-600 text-white rounded text-sm hover:bg-green-700 disabled:opacity-50"
                    >
                      {loading ? 'Envoi...' : 'Envoyer'}
                    </button>
                  </div>
                </div>
              )}
            </div>
          )}

          {showContact && (
            <div className="mt-3 pt-3 border-t bg-gray-50 -mx-4 px-4 pb-4">
              <div className="space-y-2 text-sm">
                <div className="flex items-center space-x-2">
                  <Phone className="h-4 w-4 text-gray-400" />
                  <a href={`tel:${car.agency.phone}`} className="text-blue-600 hover:underline">
                    {car.agency.phone}
                  </a>
                </div>
                <div className="flex items-center space-x-2">
                  <Mail className="h-4 w-4 text-gray-400" />
                  <a href={`mailto:${car.agency.email}`} className="text-blue-600 hover:underline">
                    {car.agency.email}
                  </a>
                </div>
                <div className="flex items-center space-x-2">
                  <MapPin className="h-4 w-4 text-gray-400" />
                  <span className="text-gray-600">{car.agency.address}</span>
                </div>
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  )
}