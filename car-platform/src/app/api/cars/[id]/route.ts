import { NextRequest, NextResponse } from 'next/server'
import { prisma } from '@/lib/prisma'
import { getTokenFromRequest, verifyToken } from '@/lib/auth'

// PATCH /api/cars/[id] - Mettre à jour une voiture
export async function PATCH(
  request: NextRequest,
  { params }: { params: { id: string } }
) {
  try {
    const token = getTokenFromRequest(request)
    if (!token) {
      return NextResponse.json(
        { error: 'Token d\'authentification manquant' },
        { status: 401 }
      )
    }

    const payload = verifyToken(token)
    if (!payload || payload.role !== 'AGENCY') {
      return NextResponse.json(
        { error: 'Accès non autorisé' },
        { status: 403 }
      )
    }

    const body = await request.json()
    const carId = params.id

    // Vérifier que la voiture appartient à l'agence de l'utilisateur
    const user = await prisma.user.findUnique({
      where: { id: payload.userId },
      include: { agency: true }
    })

    if (!user?.agency) {
      return NextResponse.json(
        { error: 'Aucune agence associée à cet utilisateur' },
        { status: 400 }
      )
    }

    const car = await prisma.car.findFirst({
      where: {
        id: carId,
        agencyId: user.agency.id
      }
    })

    if (!car) {
      return NextResponse.json(
        { error: 'Voiture non trouvée ou accès non autorisé' },
        { status: 404 }
      )
    }

    // Mettre à jour la voiture
    const updatedCar = await prisma.car.update({
      where: { id: carId },
      data: body
    })

    return NextResponse.json(updatedCar)

  } catch (error) {
    console.error('Erreur lors de la mise à jour de la voiture:', error)
    return NextResponse.json(
      { error: 'Erreur interne du serveur' },
      { status: 500 }
    )
  }
}

// DELETE /api/cars/[id] - Supprimer une voiture
export async function DELETE(
  request: NextRequest,
  { params }: { params: { id: string } }
) {
  try {
    const token = getTokenFromRequest(request)
    if (!token) {
      return NextResponse.json(
        { error: 'Token d\'authentification manquant' },
        { status: 401 }
      )
    }

    const payload = verifyToken(token)
    if (!payload || payload.role !== 'AGENCY') {
      return NextResponse.json(
        { error: 'Accès non autorisé' },
        { status: 403 }
      )
    }

    const carId = params.id

    // Vérifier que la voiture appartient à l'agence de l'utilisateur
    const user = await prisma.user.findUnique({
      where: { id: payload.userId },
      include: { agency: true }
    })

    if (!user?.agency) {
      return NextResponse.json(
        { error: 'Aucune agence associée à cet utilisateur' },
        { status: 400 }
      )
    }

    const car = await prisma.car.findFirst({
      where: {
        id: carId,
        agencyId: user.agency.id
      }
    })

    if (!car) {
      return NextResponse.json(
        { error: 'Voiture non trouvée ou accès non autorisé' },
        { status: 404 }
      )
    }

    // Supprimer la voiture
    await prisma.car.delete({
      where: { id: carId }
    })

    return NextResponse.json({ message: 'Voiture supprimée avec succès' })

  } catch (error) {
    console.error('Erreur lors de la suppression de la voiture:', error)
    return NextResponse.json(
      { error: 'Erreur interne du serveur' },
      { status: 500 }
    )
  }
}

// GET /api/cars/[id] - Récupérer une voiture spécifique
export async function GET(
  request: NextRequest,
  { params }: { params: { id: string } }
) {
  try {
    const carId = params.id

    const car = await prisma.car.findUnique({
      where: { id: carId },
      include: {
        agency: {
          select: {
            id: true,
            name: true,
            phone: true,
            email: true,
            address: true
          }
        }
      }
    })

    if (!car) {
      return NextResponse.json(
        { error: 'Voiture non trouvée' },
        { status: 404 }
      )
    }

    return NextResponse.json(car)

  } catch (error) {
    console.error('Erreur lors de la récupération de la voiture:', error)
    return NextResponse.json(
      { error: 'Erreur interne du serveur' },
      { status: 500 }
    )
  }
}